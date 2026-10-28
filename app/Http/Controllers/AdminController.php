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

    private static ?bool $hasPerformanceColumns = null;

    /**
     * Check if performance columns vote_average and genre_ids exist on the media table.
     */
    private function hasPerformanceColumns(): bool
    {
        if (self::$hasPerformanceColumns === null) {
            self::$hasPerformanceColumns = Schema::hasTable('media')
                && Schema::hasColumn('media', 'vote_average')
                && Schema::hasColumn('media', 'genre_ids');
        }
        return self::$hasPerformanceColumns;
    }

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

            $hasPerf = $this->hasPerformanceColumns();
            $now = now();
            $insertData = [];
            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['id'])) {
                    continue;
                }
                $mediaType = $item['media_type'] ?? $defaultType;
                $rawGenreIds = $item['genre_ids'] ?? [];
                $formattedGenreIds = !empty($rawGenreIds) && is_array($rawGenreIds)
                    ? ',' . implode(',', array_map('intval', $rawGenreIds)) . ','
                    : null;
                $voteAvg = isset($item['vote_average']) && is_numeric($item['vote_average'])
                    ? round((float) $item['vote_average'], 1)
                    : null;

                $row = [
                    'tmdb_id' => $item['id'],
                    'media_type' => $mediaType,
                    'title' => $item['title'] ?? $item['name'] ?? 'Unknown',
                    'poster_path' => $item['poster_path'] ?? null,
                    'data' => json_encode($item),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($hasPerf) {
                    $row['vote_average'] = $voteAvg;
                    $row['genre_ids'] = $formattedGenreIds;
                }

                $insertData[] = $row;
            }

            if (count($insertData) > 0) {
                Media::insertOrIgnore($insertData);
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to sync items to local media table: " . $e->getMessage());
        }
    }

    /**
     * Normalize genre data into a flat [id => name] dictionary.
     * Prevents TypeError in htmlspecialchars when cached genres contain raw TMDB item arrays.
     */
    private function normalizeGenres(mixed $genres): array
    {
        if ($genres instanceof Collection) {
            $genres = $genres->all();
        }
        if (!is_array($genres)) {
            return [];
        }

        $normalized = [];
        foreach ($genres as $key => $val) {
            if (is_array($val)) {
                $id = $val['id'] ?? $key;
                $name = $val['name'] ?? null;
                if ($name !== null && is_scalar($name)) {
                    $normalized[(int) $id] = (string) $name;
                }
            } elseif (is_scalar($val)) {
                $normalized[(int) $key] = (string) $val;
            }
        }

        return $normalized;
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
                if ($this->hasPerformanceColumns()) {
                    $query->where('genre_ids', 'like', "%,{$genreId},%");
                } else {
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
                    $normalized = $this->normalizeGenres($cached);
                    if (!empty($normalized)) {
                        return collect($normalized);
                    }
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
            $normalized = $this->normalizeGenres($genres);
            try {
                Cache::put('tmdb:genres:movie', $normalized, now()->addDays(7));
            } catch (\Throwable $e) {
                Log::warning("Cache write failed in getMovieGenres: " . $e->getMessage());
            }

            return collect($normalized);
        }

        return collect(self::MOVIE_GENRES);
    }

    private function getTvGenres(): Collection
    {
        try {
            if (Cache::has('tmdb:genres:tv')) {
                $cached = Cache::get('tmdb:genres:tv');
                if (!empty($cached)) {
                    $normalized = $this->normalizeGenres($cached);
                    if (!empty($normalized)) {
                        return collect($normalized);
                    }
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
            $normalized = $this->normalizeGenres($genres);
            try {
                Cache::put('tmdb:genres:tv', $normalized, now()->addDays(7));
            } catch (\Throwable $e) {
                Log::warning("Cache write failed in getTvGenres: " . $e->getMessage());
            }

            return collect($normalized);
        }

        return collect(self::TV_GENRES);
    }

    /**
     * Retrieve combined distinct genres for Anime catalog, vanishing genre 16 and any "Animation" genre.
     */
    private function getAnimeGenres(): Collection
    {
        $movieGenres = $this->getMovieGenres();
        $tvGenres = $this->getTvGenres();

        $combined = [];
        foreach ($movieGenres as $id => $name) {
            $combined[(int) $id] = $name;
        }
        foreach ($tvGenres as $id => $name) {
            $combined[(int) $id] = $name;
        }

        $filtered = [];
        foreach ($combined as $id => $name) {
            if ((int) $id === 16 || strcasecmp(trim((string) $name), 'Animation') === 0) {
                continue;
            }
            $filtered[(int) $id] = (string) $name;
        }

        asort($filtered);

        return collect($filtered);
    }

    /**
     * Query media catalog with high-performance indexes, cached counts, and robust pagination.
     */
    private function queryCatalog(Request $request, string $mediaType): LengthAwarePaginator
    {
        $genreId = $request->query('genre');
        $sort = (string) $request->query('sort', 'latest');
        $search = trim((string) ($request->query('q') ?? $request->query('search', '')));

        try {
            if (Schema::hasTable('media')) {
                // Cold-start fallback only if local catalog for this media type has zero records.
                // Uses exists() which leverages the composite index rather than scanning count().
                if (!Media::where('media_type', $mediaType)->exists()) {
                    if ($mediaType === 'tv') {
                        $this->rememberTmdb('tmdb:tv_top_rated', 'https://api.themoviedb.org/3/tv/top_rated');
                        $this->rememberTmdb('tmdb:new_tv', 'https://api.themoviedb.org/3/tv/popular');
                    } else {
                        $this->rememberTmdb('tmdb:top_rated', 'https://api.themoviedb.org/3/movie/top_rated');
                        $this->rememberTmdb('tmdb:popular_movies', 'https://api.themoviedb.org/3/movie/popular');
                    }
                }

                $query = Media::where('media_type', $mediaType);

                // Filter by title keyword
                if ($search !== '') {
                    $query->where('title', 'like', "%{$search}%");
                }

                // Filter by genre
                if (!empty($genreId)) {
                    $genreIdInt = (int) $genreId;
                    if ($this->hasPerformanceColumns()) {
                        $query->where('genre_ids', 'like', "%,{$genreIdInt},%");
                    } else {
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
                }

                // Sorting
                if ($sort === 'rating') {
                    if ($this->hasPerformanceColumns()) {
                        $query->orderBy('vote_average', 'desc')->orderBy('id', 'desc');
                    } else {
                        $driver = DB::connection()->getDriverName();
                        if ($driver === 'sqlite') {
                            $query->orderByRaw("CAST(json_extract(data, '$.vote_average') AS REAL) DESC");
                        } else {
                            $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.vote_average')) AS DECIMAL(4,1)) DESC");
                        }
                    }
                } elseif ($sort === 'title_asc') {
                    $query->orderBy('title', 'asc')->orderBy('id', 'desc');
                } elseif ($sort === 'title_desc') {
                    $query->orderBy('title', 'desc')->orderBy('id', 'desc');
                } else {
                    $query->latest('id');
                }

                $page = max(1, (int) $request->query('page', 1));
                $perPage = 24;

                // Cache catalog total count to avoid repeating expensive COUNT(*) scans on 50k+ records
                $countCacheKey = "media:count:{$mediaType}:" . md5($search . '|' . ($genreId ?? ''));
                $total = app()->runningUnitTests()
                    ? (clone $query)->count()
                    : (int) Cache::remember($countCacheKey, now()->addMinutes(10), function () use ($query) {
                        return (clone $query)->count();
                    });

                $items = (clone $query)->forPage($page, $perPage)->get();

                return new LengthAwarePaginator(
                    $items,
                    $total,
                    $perPage,
                    $page,
                    [
                        'path' => $request->url(),
                        'query' => $request->query(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning("Error querying {$mediaType} catalog: " . $e->getMessage());
        }

        return new LengthAwarePaginator([], 0, 24, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    /**
     * Query anime catalog with baseline Animation genre enforcement (ID 16),
     * format filtering (all/movie/tv), and high-performance indexed queries.
     */
    private function queryAnimeCatalog(Request $request): LengthAwarePaginator
    {
        $format = (string) $request->query('format', '');
        $genreId = $request->query('genre');
        $sort = (string) $request->query('sort', 'latest');
        $search = trim((string) ($request->query('q') ?? $request->query('search', '')));

        try {
            if (Schema::hasTable('media')) {
                // Cold-start fallback only if local anime catalog has zero records
                if ($this->hasPerformanceColumns()) {
                    $hasAnime = Media::where('genre_ids', 'like', '%,16,%')->exists();
                } else {
                    $hasAnime = Media::where('data', 'like', '%"id":16%')->exists();
                }

                if (!$hasAnime) {
                    $this->rememberTmdb('tmdb:tv_top_anime', 'https://api.themoviedb.org/3/discover/tv', [
                        'sort_by' => 'vote_count.desc',
                        'with_genres' => 16,
                    ]);
                    $this->rememberTmdb('tmdb:anime', 'https://api.themoviedb.org/3/discover/movie', [
                        'with_genres' => 16,
                    ]);
                }

                $query = Media::query();

                // Format filter: all, tv, movie
                if ($format === 'tv') {
                    $query->where('media_type', 'tv');
                } elseif ($format === 'movie') {
                    $query->where('media_type', 'movie');
                } else {
                    $query->whereIn('media_type', ['movie', 'tv']);
                }

                // Automatic Baseline Constraint: Must always include genre 16 (Animation)
                if ($this->hasPerformanceColumns()) {
                    $query->where('genre_ids', 'like', '%,16,%');
                } else {
                    $driver = DB::connection()->getDriverName();
                    if ($driver === 'mysql') {
                        $query->where(function ($q) {
                            $q->whereJsonContains('data->genre_ids', 16)
                              ->orWhereJsonContains('data->genre_ids', '16')
                              ->orWhere('data', 'like', '%"id":16%')
                              ->orWhere('data', 'like', '%[16,%')
                              ->orWhere('data', 'like', '%,16,%')
                              ->orWhere('data', 'like', '%,16]%')
                              ->orWhere('data', 'like', '%[16]%');
                        });
                    } else {
                        $query->where(function ($q) {
                            $q->where('data', 'like', '%"genre_ids":%[16,%')
                              ->orWhere('data', 'like', '%"genre_ids":%,16,%')
                              ->orWhere('data', 'like', '%"genre_ids":%,16]%')
                              ->orWhere('data', 'like', '%"genre_ids":%[16]%')
                              ->orWhere('data', 'like', '%"id":16%');
                        });
                    }
                }

                // Additional Genre Constraint: Match chosen genre ID alongside genre 16
                if (!empty($genreId) && (int) $genreId !== 16) {
                    $genreIdInt = (int) $genreId;
                    if ($this->hasPerformanceColumns()) {
                        $query->where('genre_ids', 'like', "%,{$genreIdInt},%");
                    } else {
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
                }

                // Filter by title keyword
                if ($search !== '') {
                    $query->where('title', 'like', "%{$search}%");
                }

                // Sorting
                if ($sort === 'rating') {
                    if ($this->hasPerformanceColumns()) {
                        $query->orderBy('vote_average', 'desc')->orderBy('id', 'desc');
                    } else {
                        $driver = DB::connection()->getDriverName();
                        if ($driver === 'sqlite') {
                            $query->orderByRaw("CAST(json_extract(data, '$.vote_average') AS REAL) DESC");
                        } else {
                            $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.vote_average')) AS DECIMAL(4,1)) DESC");
                        }
                    }
                } elseif ($sort === 'title_asc') {
                    $query->orderBy('title', 'asc')->orderBy('id', 'desc');
                } elseif ($sort === 'title_desc') {
                    $query->orderBy('title', 'desc')->orderBy('id', 'desc');
                } else {
                    $query->latest('id');
                }

                $page = max(1, (int) $request->query('page', 1));
                $perPage = 24;

                // Cache anime catalog total count
                $countCacheKey = 'media:count:anime:' . md5($search . '|' . $format . '|' . ($genreId ?? ''));
                $total = app()->runningUnitTests()
                    ? (clone $query)->count()
                    : (int) Cache::remember($countCacheKey, now()->addMinutes(10), function () use ($query) {
                        return (clone $query)->count();
                    });

                $items = (clone $query)->forPage($page, $perPage)->get();

                return new LengthAwarePaginator(
                    $items,
                    $total,
                    $perPage,
                    $page,
                    [
                        'path' => $request->url(),
                        'query' => $request->query(),
                    ]
                );
            }
        } catch (\Throwable $e) {
            Log::warning("Error querying anime catalog: " . $e->getMessage());
        }

        return new LengthAwarePaginator([], 0, 24, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
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

        $shows = $this->queryCatalog($request, 'tv');

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

    public function animation(Request $request)
    {
        $format = (string) $request->query('format', '');
        $genreId = $request->query('genre');
        $sort = (string) $request->query('sort', 'latest');
        $search = trim((string) ($request->query('q') ?? $request->query('search', '')));

        $items = $this->queryAnimeCatalog($request);
        $animeGenres = $this->getAnimeGenres();

        return view('Animation.animation', [
            'items' => $items,
            'topAn' => $items, // Backward compatibility
            'anime' => $items, // Backward compatibility
            'genres' => $animeGenres,
            'selectedFormat' => $format,
            'selectedGenre' => $genreId,
            'selectedSort' => $sort,
            'searchQuery' => $search,
        ]);
    }

    public function movieList(Request $request)
    {
        $genreId = $request->query('genre');
        $sort = (string) $request->query('sort', 'latest');
        $search = trim((string) ($request->query('q') ?? $request->query('search', '')));

        $movies = $this->queryCatalog($request, 'movie');

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
