<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    private const MOVIE_GENRES = [
        28 => 'Action',
        12 => 'Adventure',
        16 => 'Animation',
        35 => 'Comedy',
        80 => 'Crime',
        99 => 'Documentary',
        18 => 'Drama',
        10751 => 'Family',
        14 => 'Fantasy',
        36 => 'History',
        27 => 'Horror',
        10402 => 'Music',
        9648 => 'Mystery',
        10749 => 'Romance',
        878 => 'Science Fiction',
        10770 => 'TV Movie',
        53 => 'Thriller',
        10752 => 'War',
        37 => 'Western',
    ];

    private const TV_GENRES = [
        10759 => 'Action & Adventure',
        16 => 'Animation',
        35 => 'Comedy',
        80 => 'Crime',
        99 => 'Documentary',
        18 => 'Drama',
        10762 => 'Kids',
        9648 => 'Mystery',
        10763 => 'News',
        10764 => 'Reality',
        10765 => 'Sci-Fi & Fantasy',
        10766 => 'Soap',
        10767 => 'Talk',
        10768 => 'War & Politics',
        37 => 'Western',
    ];

    /**
     * Safely fetch data from TMDB API with timeout and retry.
     * Returns null on failure so failed responses are not cached long-term.
     */
    private function fetchTmdb(string $endpoint, array $query = [], string $key = 'results'): ?array
    {
        try {
            $token = config('services.tmdb.token');
            if (empty($token)) {
                return null;
            }

            $response = Http::withToken($token)
                ->timeout(5)
                ->retry(2, 200)
                ->get($endpoint, $query);

            if ($response->successful()) {
                return $response->json($key) ?? [];
            }

            Log::warning("TMDB request failed for {$endpoint} with status {$response->status()}");
        } catch (\Throwable $e) {
            Log::warning("TMDB request exception for {$endpoint}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Store retrieved items into local Media catalog to reduce future TMDB API reliance.
     */
    private function syncLocalMedia(array $items, string $defaultType = 'movie'): void
    {
        if (empty($items)) {
            return;
        }

        try {
            if (!Schema::hasTable('media')) {
                return;
            }

            $now = now();
            $insertData = [];
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['id'])) {
                    continue;
                }
                $mediaType = $item['media_type'] ?? $defaultType;
                $insertData[] = [
                    'tmdb_id' => $item['id'],
                    'media_type' => $mediaType,
                    'title' => $item['title'] ?? $item['name'] ?? 'Unknown',
                    'poster_path' => $item['poster_path'] ?? null,
                    'data' => json_encode($item),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (count($insertData) > 0) {
                Media::insertOrIgnore($insertData);
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to sync items to local media table: " . $e->getMessage());
        }
    }

    /**
     * Retrieve fallback items from local database when external TMDB is offline or unseeded.
     */
    private function getLocalMediaFallback(string $type, int $limit = 20, ?int $genreId = null): array
    {
        try {
            if (!Schema::hasTable('media')) {
                return [];
            }

            $query = Media::where('media_type', $type);

            if ($genreId !== null) {
                $driver = DB::connection()->getDriverName();
                if ($driver === 'mysql') {
                    $query->where(function ($q) use ($genreId) {
                        $q->whereJsonContains('data->genre_ids', $genreId)
                          ->orWhere('data', 'like', '%"id":' . $genreId . '%');
                    });
                } else {
                    $query->where(function ($q) use ($genreId) {
                        $q->where('data', 'like', '%"genre_ids":%[' . $genreId . ',%')
                          ->orWhere('data', 'like', '%"genre_ids":%,' . $genreId . ',%')
                          ->orWhere('data', 'like', '%"genre_ids":%,' . $genreId . ']%')
                          ->orWhere('data', 'like', '%"genre_ids":%[' . $genreId . ']%')
                          ->orWhere('data', 'like', '%"id":' . $genreId . '%');
                    });
                }
            }

            return $query->latest('id')
                ->take($limit)
                ->get()
                ->map(function ($media) {
                    $data = is_array($media->data) ? $media->data : (json_decode($media->data, true) ?? []);
                    $data['id'] = $media->tmdb_id;
                    $data['title'] = $media->title;
                    $data['name'] = $media->title;
                    $data['poster_path'] = $media->poster_path;
                    return $data;
                })
                ->toArray();
        } catch (\Throwable $e) {
            Log::warning("Local media fallback query failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch from TMDB and cache only on successful response, with full resilience to cache backend failures.
     */
    private function rememberTmdb(string $cacheKey, string $endpoint, array $query = [], string $resultsKey = 'results', int $ttlHours = 4): array
    {
        try {
            if (Cache::has($cacheKey)) {
                $cached = Cache::get($cacheKey);
                if (is_array($cached)) {
                    return $cached;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Cache read failed for key {$cacheKey}: " . $e->getMessage());
        }

        $data = $this->fetchTmdb($endpoint, $query, $resultsKey);

        if ($data !== null) {
            try {
                Cache::put($cacheKey, $data, now()->addHours($ttlHours));
            } catch (\Throwable $e) {
                Log::warning("Cache write failed for key {$cacheKey}: " . $e->getMessage());
            }

            if ($resultsKey === 'results') {
                $inferredType = str_contains($endpoint, '/tv') ? 'tv' : 'movie';
                $this->syncLocalMedia($data, $inferredType);
            }
            return $data;
        }

        return [];
    }

    private function getMovieGenres(): Collection
    {
        try {
            if (Cache::has('tmdb:genres:movie')) {
                $cached = Cache::get('tmdb:genres:movie');
                if (!empty($cached)) {
                    return collect($cached);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Cache read failed in getMovieGenres: " . $e->getMessage());
        }

        if (app()->runningUnitTests() || empty(config('services.tmdb.token'))) {
            return collect(self::MOVIE_GENRES);
        }

        $genres = $this->fetchTmdb('https://api.themoviedb.org/3/genre/movie/list', [], 'genres');
        if (!empty($genres)) {
            $mapped = collect($genres)->mapWithKeys(function ($genre) {
                return [$genre['id'] => $genre['name']];
            });

            try {
                Cache::put('tmdb:genres:movie', $mapped->toArray(), now()->addDays(7));
            } catch (\Throwable $e) {
                Log::warning("Cache write failed in getMovieGenres: " . $e->getMessage());
            }

            return $mapped;
        }

        return collect(self::MOVIE_GENRES);
    }

    private function getTvGenres(): Collection
    {
        try {
            if (Cache::has('tmdb:genres:tv')) {
                $cached = Cache::get('tmdb:genres:tv');
                if (!empty($cached)) {
                    return collect($cached);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Cache read failed in getTvGenres: " . $e->getMessage());
        }

        if (app()->runningUnitTests() || empty(config('services.tmdb.token'))) {
            return collect(self::TV_GENRES);
        }

        $genres = $this->fetchTmdb('https://api.themoviedb.org/3/genre/tv/list', [], 'genres');
        if (!empty($genres)) {
            $mapped = collect($genres)->mapWithKeys(function ($genre) {
                return [$genre['id'] => $genre['name']];
            });

            try {
                Cache::put('tmdb:genres:tv', $mapped->toArray(), now()->addDays(7));
            } catch (\Throwable $e) {
                Log::warning("Cache write failed in getTvGenres: " . $e->getMessage());
            }

            return $mapped;
        }

        return collect(self::TV_GENRES);
    }

    public function index()
    {
        $genreId = 16;

        $popularMovies = $this->rememberTmdb('tmdb:popular_movies', 'https://api.themoviedb.org/3/movie/popular');
        if (empty($popularMovies)) {
            $popularMovies = $this->getLocalMediaFallback('movie', 20);
        }

        $nowPlaying = $this->rememberTmdb('tmdb:now_playing', 'https://api.themoviedb.org/3/movie/now_playing');
        if (empty($nowPlaying)) {
            $nowPlaying = $this->getLocalMediaFallback('movie', 20);
        }

        $topRated = $this->rememberTmdb('tmdb:top_rated', 'https://api.themoviedb.org/3/movie/top_rated');
        if (empty($topRated)) {
            $topRated = $this->getLocalMediaFallback('movie', 20);
        }

        $newtv = $this->rememberTmdb('tmdb:new_tv', 'https://api.themoviedb.org/3/tv/popular');
        if (empty($newtv)) {
            $newtv = $this->getLocalMediaFallback('tv', 20);
        }

        $anime = $this->rememberTmdb('tmdb:anime', 'https://api.themoviedb.org/3/discover/movie', ['with_genres' => $genreId]);
        if (empty($anime)) {
            $anime = $this->getLocalMediaFallback('movie', 20, $genreId);
        }

        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();

        return view('movies.index', [
            'popularMovies' => $popularMovies,
            'nowPlaying' => $nowPlaying,
            'topRated' => $topRated,
            'random' => [],
            'newtv' => $newtv,
            'anime' => $anime,
            'genresTv' => $genresTv,
            'genres' => $genres,
        ]);
    }

    public function tvshow(Request $request)
    {
        $genreId = $request->query('genre');
        $sort = (string) $request->query('sort', 'latest');
        $search = trim((string) ($request->query('q') ?? $request->query('search', '')));
        $shows = null;

        try {
            if (Schema::hasTable('media')) {
                // Cold-start fallback only if local TV catalog is completely unpopulated
                if (Media::where('media_type', 'tv')->count() === 0) {
                    $this->rememberTmdb('tmdb:tv_top_rated', 'https://api.themoviedb.org/3/tv/top_rated');
                    $this->rememberTmdb('tmdb:new_tv', 'https://api.themoviedb.org/3/tv/popular');
                }

                $query = Media::where('media_type', 'tv');

                // Filter by title keyword
                if ($search !== '') {
                    $query->where('title', 'like', "%{$search}%");
                }

                // Filter by genre
                if (!empty($genreId)) {
                    $genreIdInt = (int) $genreId;
                    $driver = DB::connection()->getDriverName();
                    if ($driver === 'mysql') {
                        $query->where(function ($q) use ($genreIdInt, $genreId) {
                            $q->whereJsonContains('data->genre_ids', $genreIdInt)
                              ->orWhereJsonContains('data->genre_ids', (string) $genreId)
                              ->orWhere('data', 'like', '%"id":' . $genreIdInt . '%')
                              ->orWhere('data', 'like', '%[' . $genreIdInt . ',%')
                              ->orWhere('data', 'like', '%,' . $genreIdInt . ',%')
                              ->orWhere('data', 'like', '%,' . $genreIdInt . ']%')
                              ->orWhere('data', 'like', '%[' . $genreIdInt . ']%');
                        });
                    } else {
                        $query->where(function ($q) use ($genreIdInt) {
                            $q->where('data', 'like', '%"genre_ids":%[' . $genreIdInt . ',%')
                              ->orWhere('data', 'like', '%"genre_ids":%,' . $genreIdInt . ',%')
                              ->orWhere('data', 'like', '%"genre_ids":%,' . $genreIdInt . ']%')
                              ->orWhere('data', 'like', '%"genre_ids":%[' . $genreIdInt . ']%')
                              ->orWhere('data', 'like', '%"id":' . $genreIdInt . '%');
                        });
                    }
                }

                // Sort ordering
                if ($sort === 'rating') {
                    $driver = DB::connection()->getDriverName();
                    if ($driver === 'sqlite') {
                        $query->orderByRaw("CAST(json_extract(data, '$.vote_average') AS REAL) DESC");
                    } else {
                        $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.vote_average')) AS DECIMAL(4,1)) DESC");
                    }
                } elseif ($sort === 'title_asc') {
                    $query->orderBy('title', 'asc');
                } elseif ($sort === 'title_desc') {
                    $query->orderBy('title', 'desc');
                } else {
                    $query->latest('id');
                }

                /** @var LengthAwarePaginator $shows */
                $shows = $query->paginate(24);
                $shows->appends($request->query());
            }
        } catch (\Throwable $e) {
            Log::warning("Error querying tv shows catalog: " . $e->getMessage());
        }

        if ($shows === null) {
            $shows = new LengthAwarePaginator([], 0, 24, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();

        return view('tvshow', [
            'shows' => $shows,
            'top' => $shows, // Backward compatibility
            'genresTv' => $genresTv,
            'genres' => $genres,
            'selectedGenre' => $genreId,
            'selectedSort' => $sort,
            'searchQuery' => $search,
        ]);
    }

    public function animation()
    {
        $genreId = 16;

        $popularMovies = $this->rememberTmdb('tmdb:popular_movies', 'https://api.themoviedb.org/3/movie/popular');
        if (empty($popularMovies)) {
            $popularMovies = $this->getLocalMediaFallback('movie', 20);
        }

        $topAn = $this->rememberTmdb('tmdb:tv_top_anime', 'https://api.themoviedb.org/3/discover/tv', [
            'sort_by' => 'vote_count.desc',
            'with_genres' => $genreId,
        ]);
        if (empty($topAn)) {
            $topAn = $this->getLocalMediaFallback('tv', 20, $genreId);
        }

        $newtv = $this->rememberTmdb('tmdb:tv_airing_today', 'https://api.themoviedb.org/3/tv/airing_today');
        if (empty($newtv)) {
            $newtv = $this->getLocalMediaFallback('tv', 20);
        }

        $anime = $this->rememberTmdb('tmdb:anime', 'https://api.themoviedb.org/3/discover/movie', ['with_genres' => $genreId]);
        if (empty($anime)) {
            $anime = $this->getLocalMediaFallback('movie', 20, $genreId);
        }

        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();

        return view('Animation.animation', [
            'popularMovies' => $popularMovies,
            'newtv' => $newtv,
            'anime' => $anime,
            'genresTv' => $genresTv,
            'genres' => $genres,
            'topAn' => $topAn,
        ]);
    }

    public function movieList(Request $request)
    {
        $genreId = $request->query('genre');
        $sort = (string) $request->query('sort', 'latest');
        $search = trim((string) ($request->query('q') ?? $request->query('search', '')));
        $movies = null;

        try {
            if (Schema::hasTable('media')) {
                // Cold-start fallback only if local movie catalog is completely unpopulated
                if (Media::where('media_type', 'movie')->count() === 0) {
                    $this->rememberTmdb('tmdb:top_rated', 'https://api.themoviedb.org/3/movie/top_rated');
                    $this->rememberTmdb('tmdb:popular_movies', 'https://api.themoviedb.org/3/movie/popular');
                }

                $query = Media::where('media_type', 'movie');

                // Filter by title keyword
                if ($search !== '') {
                    $query->where('title', 'like', "%{$search}%");
                }

                // Filter by genre
                if (!empty($genreId)) {
                    $genreIdInt = (int) $genreId;
                    $driver = DB::connection()->getDriverName();
                    if ($driver === 'mysql') {
                        $query->where(function ($q) use ($genreIdInt, $genreId) {
                            $q->whereJsonContains('data->genre_ids', $genreIdInt)
                              ->orWhereJsonContains('data->genre_ids', (string) $genreId)
                              ->orWhere('data', 'like', '%"id":' . $genreIdInt . '%')
                              ->orWhere('data', 'like', '%[' . $genreIdInt . ',%')
                              ->orWhere('data', 'like', '%,' . $genreIdInt . ',%')
                              ->orWhere('data', 'like', '%,' . $genreIdInt . ']%')
                              ->orWhere('data', 'like', '%[' . $genreIdInt . ']%');
                        });
                    } else {
                        $query->where(function ($q) use ($genreIdInt) {
                            $q->where('data', 'like', '%"genre_ids":%[' . $genreIdInt . ',%')
                              ->orWhere('data', 'like', '%"genre_ids":%,' . $genreIdInt . ',%')
                              ->orWhere('data', 'like', '%"genre_ids":%,' . $genreIdInt . ']%')
                              ->orWhere('data', 'like', '%"genre_ids":%[' . $genreIdInt . ']%')
                              ->orWhere('data', 'like', '%"id":' . $genreIdInt . '%');
                        });
                    }
                }

                // Sort ordering
                if ($sort === 'rating') {
                    $driver = DB::connection()->getDriverName();
                    if ($driver === 'sqlite') {
                        $query->orderByRaw("CAST(json_extract(data, '$.vote_average') AS REAL) DESC");
                    } else {
                        $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.vote_average')) AS DECIMAL(4,1)) DESC");
                    }
                } elseif ($sort === 'title_asc') {
                    $query->orderBy('title', 'asc');
                } elseif ($sort === 'title_desc') {
                    $query->orderBy('title', 'desc');
                } else {
                    $query->latest('id');
                }

                /** @var LengthAwarePaginator $movies */
                $movies = $query->paginate(24);
                $movies->appends($request->query());
            }
        } catch (\Throwable $e) {
            Log::warning("Error querying movies catalog: " . $e->getMessage());
        }

        if ($movies === null) {
            $movies = new LengthAwarePaginator([], 0, 24, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        $genres = $this->getMovieGenres();

        return view('movieList.movie', [
            'movies' => $movies,
            'topRated' => $movies, // Backward compatibility
            'genres' => $genres,
            'selectedGenre' => $genreId,
            'selectedSort' => $sort,
            'searchQuery' => $search,
        ]);
    }
}
