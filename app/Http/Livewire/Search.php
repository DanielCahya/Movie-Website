<?php

namespace App\Http\Livewire;

use App\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Search extends Component
{
    public $search = '';

    public function render()
    {
        $term = trim((string) $this->search);
        $hasilcari = [];

        if (mb_strlen($term) >= 2) {
            $cacheKey = 'navbar:search:v1:' . md5(mb_strtolower($term));

            $hasilcari = app()->runningUnitTests()
                ? $this->performSearch($term)
                : Cache::remember($cacheKey, now()->addMinutes(30), function () use ($term) {
                    return $this->performSearch($term);
                });
        }

        return view('livewire.search', [
            'hasilcari' => collect($hasilcari)->take(10),
        ]);
    }

    /**
     * Perform fast search locally first, falling back to TMDB API if not found.
     *
     * @param string $term
     * @return array
     */
    protected function performSearch(string $term): array
    {
        $selectCols = ['tmdb_id', 'media_type', 'title', 'poster_path'];

        try {
            // 1. Prefix match (utilizes B-Tree index idx_media_title with sub-millisecond seek)
            $prefixMatches = Media::select($selectCols)
                ->where('title', 'like', $term . '%')
                ->limit(10)
                ->get();

            $totalFound = $prefixMatches->count();

            // 2. Substring match if prefix match produced fewer than 10 items
            if ($totalFound < 10) {
                $excludeIds = $prefixMatches->pluck('tmdb_id')->toArray();
                $remainingLimit = 10 - $totalFound;

                $substringMatches = Media::select($selectCols)
                    ->where('title', 'like', '%' . $term . '%')
                    ->whereNotIn('tmdb_id', $excludeIds)
                    ->limit($remainingLimit)
                    ->get();

                $localResults = $prefixMatches->concat($substringMatches);
            } else {
                $localResults = $prefixMatches;
            }

            if ($localResults->isNotEmpty()) {
                return $localResults->map(function ($media) {
                    return [
                        'id' => (int) $media->tmdb_id,
                        'tmdb_id' => (int) $media->tmdb_id,
                        'media_type' => (string) ($media->media_type ?? 'movie'),
                        'title' => (string) $media->title,
                        'poster_path' => $media->poster_path,
                    ];
                })->values()->toArray();
            }
        } catch (\Throwable $e) {
            Log::warning('Navbar local search error: ' . $e->getMessage());
        }

        // 3. Fallback to TMDB API if local database has no matches
        return $this->fetchFromTmdb($term);
    }

    /**
     * Query TMDB API when local search yields zero matches.
     *
     * @param string $term
     * @return array
     */
    protected function fetchFromTmdb(string $term): array
    {
        $token = config('services.tmdb.token');
        if (empty($token)) {
            return [];
        }

        try {
            $response = Http::timeout(3)
                ->withToken($token)
                ->get('https://api.themoviedb.org/3/search/multi', [
                    'query' => $term,
                    'include_adult' => 'false',
                ]);

            if (!$response->successful()) {
                return [];
            }

            $results = $response->json('results') ?? [];

            // Filter to only movies and TV shows
            $filtered = collect($results)->filter(function ($item) {
                return in_array($item['media_type'] ?? '', ['movie', 'tv']);
            })->values()->take(10);

            $insertData = [];
            $formatted = [];
            $now = now();

            foreach ($filtered as $item) {
                $tmdbId = (int) ($item['id'] ?? 0);
                $mediaType = (string) ($item['media_type'] ?? 'movie');
                $title = (string) ($item['title'] ?? $item['name'] ?? 'Unknown');
                $posterPath = $item['poster_path'] ?? null;

                if ($tmdbId <= 0) {
                    continue;
                }

                $formatted[] = [
                    'id' => $tmdbId,
                    'tmdb_id' => $tmdbId,
                    'media_type' => $mediaType,
                    'title' => $title,
                    'poster_path' => $posterPath,
                ];

                $genreIds = !empty($item['genre_ids']) && is_array($item['genre_ids'])
                    ? ',' . implode(',', $item['genre_ids']) . ','
                    : null;
                $voteAverage = isset($item['vote_average']) ? round((float) $item['vote_average'], 1) : null;

                $insertData[] = [
                    'tmdb_id' => $tmdbId,
                    'media_type' => $mediaType,
                    'title' => $title,
                    'poster_path' => $posterPath,
                    'vote_average' => $voteAverage,
                    'genre_ids' => $genreIds,
                    'data' => json_encode($item),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($insertData)) {
                Media::insertOrIgnore($insertData);
            }

            return $formatted;
        } catch (\Throwable $e) {
            Log::warning('Navbar TMDB search fallback error: ' . $e->getMessage());
            return [];
        }
    }
}
