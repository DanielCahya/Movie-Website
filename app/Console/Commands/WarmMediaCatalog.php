<?php

namespace App\Console\Commands;

use App\Http\Controllers\ShowController;
use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WarmMediaCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:warm 
                            {--pages=2 : Number of pages to fetch per category (20 titles per page)}
                            {--type=all : Media type to warm (all, movie, tv, anime)}
                            {--deep : Fetch full trailers, cast, and credits for top titles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pre-populate local database with popular, top-rated, and anime media from TMDB to eliminate real-time user latency';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $token = config('services.tmdb.token');
        if (empty($token)) {
            $this->error('TMDB API token is not configured. Please set TMDB_TOKEN in your .env file.');
            return self::FAILURE;
        }

        $pages = max(1, min((int) $this->option('pages'), 50));
        $typeOption = strtolower((string) $this->option('type'));
        $isDeep = (bool) $this->option('deep');

        $categories = [];

        if (in_array($typeOption, ['all', 'movie'], true)) {
            $categories[] = [
                'type' => 'movie',
                'name' => 'Popular Movies',
                'endpoint' => 'https://api.themoviedb.org/3/movie/popular',
                'query' => [],
            ];
            $categories[] = [
                'type' => 'movie',
                'name' => 'Top Rated Movies',
                'endpoint' => 'https://api.themoviedb.org/3/movie/top_rated',
                'query' => [],
            ];
        }

        if (in_array($typeOption, ['all', 'tv'], true)) {
            $categories[] = [
                'type' => 'tv',
                'name' => 'Popular TV Shows',
                'endpoint' => 'https://api.themoviedb.org/3/tv/popular',
                'query' => [],
            ];
            $categories[] = [
                'type' => 'tv',
                'name' => 'Top Rated TV Shows',
                'endpoint' => 'https://api.themoviedb.org/3/tv/top_rated',
                'query' => [],
            ];
        }

        if (in_array($typeOption, ['all', 'anime'], true)) {
            $categories[] = [
                'type' => 'tv',
                'name' => 'Top Anime Series',
                'endpoint' => 'https://api.themoviedb.org/3/discover/tv',
                'query' => [
                    'with_genres' => 16,
                    'sort_by' => 'vote_count.desc',
                ],
            ];
            $categories[] = [
                'type' => 'tv',
                'name' => 'Popular Anime Series',
                'endpoint' => 'https://api.themoviedb.org/3/discover/tv',
                'query' => [
                    'with_genres' => 16,
                    'sort_by' => 'popularity.desc',
                ],
            ];
            $categories[] = [
                'type' => 'tv',
                'name' => 'Japanese Anime Series',
                'endpoint' => 'https://api.themoviedb.org/3/discover/tv',
                'query' => [
                    'with_genres' => 16,
                    'with_original_language' => 'ja',
                    'sort_by' => 'popularity.desc',
                ],
            ];
            $categories[] = [
                'type' => 'movie',
                'name' => 'Top Anime Movies',
                'endpoint' => 'https://api.themoviedb.org/3/discover/movie',
                'query' => [
                    'with_genres' => 16,
                    'sort_by' => 'vote_count.desc',
                ],
            ];
            $categories[] = [
                'type' => 'movie',
                'name' => 'Popular Animated Movies',
                'endpoint' => 'https://api.themoviedb.org/3/discover/movie',
                'query' => [
                    'with_genres' => 16,
                    'sort_by' => 'popularity.desc',
                ],
            ];
        }

        $totalInserted = 0;
        $totalProcessed = 0;
        $showController = $isDeep ? app(ShowController::class) : null;
        $hasPerformanceColumns = Schema::hasTable('media')
            && Schema::hasColumn('media', 'vote_average')
            && Schema::hasColumn('media', 'genre_ids');

        $this->info("Starting catalog warmup: {$pages} page(s) per category [type: {$typeOption}]");

        foreach ($categories as $cat) {
            $this->line("\n<fg=cyan;options=bold>==> Syncing {$cat['name']}...</>");

            for ($page = 1; $page <= $pages; $page++) {
                try {
                    $requestQuery = array_merge($cat['query'] ?? [], ['page' => $page]);

                    $response = Http::withToken($token)
                        ->timeout(8)
                        ->retry(2, 200)
                        ->get($cat['endpoint'], $requestQuery);

                    if (!$response->successful()) {
                        $this->warn("  Failed to fetch page {$page} (HTTP {$response->status()})");
                        Log::warning("media:warm failed for {$cat['endpoint']} page {$page} with status {$response->status()}");
                        continue;
                    }

                    $results = $response->json('results') ?? [];
                    if (empty($results)) {
                        break;
                    }

                    $insertData = [];
                    $now = now();

                    foreach ($results as $item) {
                        if (!is_array($item) || !isset($item['id'])) {
                            continue;
                        }

                        $totalProcessed++;
                        $mediaType = $cat['type'];
                        $rawGenreIds = $item['genre_ids'] ?? [];

                        // Ensure anime categories always include genre 16
                        if (!empty($cat['query']['with_genres']) && !in_array(16, $rawGenreIds, true)) {
                            $rawGenreIds[] = 16;
                            $item['genre_ids'] = $rawGenreIds;
                        }

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

                        if ($hasPerformanceColumns) {
                            $row['vote_average'] = $voteAvg;
                            $row['genre_ids'] = $formattedGenreIds;
                        }

                        $insertData[] = $row;

                        // If deep warming is requested, fetch full rich details for page 1 items
                        if ($isDeep && $page === 1 && $showController) {
                            $showController->fetchMedia($mediaType, $item['id']);
                            if (!app()->runningUnitTests()) {
                                usleep(50000); // 50ms pause to respect API limits
                            }
                        }
                    }

                    if (!empty($insertData)) {
                        $inserted = Media::insertOrIgnore($insertData);
                        $totalInserted += $inserted;
                        $this->line("  Page {$page}: processed " . count($insertData) . " titles ({$inserted} newly inserted)");
                    }

                    // Delay between page requests outside unit tests
                    if (!app()->runningUnitTests()) {
                        usleep(100000); // 100ms
                    }
                } catch (\Throwable $e) {
                    $this->error("  Exception on page {$page}: " . $e->getMessage());
                    Log::error("media:warm exception: " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info("Catalog warmup complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Titles Processed', $totalProcessed],
                ['New Titles Inserted', $totalInserted],
            ]
        );

        return self::SUCCESS;
    }
}
