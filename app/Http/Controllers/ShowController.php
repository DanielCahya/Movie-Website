<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MediaComment;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShowController extends Controller
{
    public function fetchMedia($type, $id)
    {
        // 1. Try to fetch from local MySQL database
        $localMedia = Media::where('tmdb_id', $id)->where('media_type', $type)->first();

        if ($localMedia && $localMedia->data) {
            $data = is_string($localMedia->data) ? json_decode($localMedia->data, true) : $localMedia->data;
            // Verify if local record contains rich details (videos or credits) AND is fresh (within 7 days)
            $hasRichDetails = !empty($data['videos']) || !empty($data['credits']);
            $isFresh = $localMedia->updated_at && $localMedia->updated_at->gt(now()->subDays(7));

            if ($hasRichDetails && $isFresh) {
                // Heal missing performance columns if null
                if ($localMedia->genre_ids === null || $localMedia->vote_average === null) {
                    $this->healMediaRecord($localMedia, $data);
                }
                return $data;
            }
        }

        // 2. If not in DB, incomplete stub, or stale: fetch from TMDB with timeout & retry
        $token = config('services.tmdb.token');
        if (empty($token)) {
            // Fallback to local data if token is missing
            if (!empty($localMedia) && !empty($localMedia->data)) {
                return is_string($localMedia->data) ? json_decode($localMedia->data, true) : $localMedia->data;
            }
            return [];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->retry(2, 200)
                ->get("https://api.themoviedb.org/3/{$type}/{$id}?append_to_response=credits,videos,images,similar");

            if ($response->successful()) {
                $tmdbData = $response->json();

                // 3. Save or update local database using updateOrCreate to prevent duplicate key crashes
                if (!isset($tmdbData['success']) || $tmdbData['success'] !== false) {
                    $this->persistMediaDetails($type, (int) $id, $tmdbData);
                }

                return $tmdbData;
            }

            Log::warning("TMDB fetchMedia returned HTTP {$response->status()} for {$type}/{$id}");
        } catch (\Throwable $e) {
            Log::warning("TMDB fetchMedia exception for {$type}/{$id}: " . $e->getMessage());
        }

        // Fallback to existing local data if TMDB request failed (outage resilience)
        if (!empty($localMedia) && !empty($localMedia->data)) {
            return is_string($localMedia->data) ? json_decode($localMedia->data, true) : $localMedia->data;
        }

        return [];
    }

    /**
     * Persist or update rich TMDB media details, extracting genre_ids and vote_average.
     */
    protected function persistMediaDetails(string $type, int $id, array $tmdbData): void
    {
        $rawGenres = $tmdbData['genres'] ?? [];
        $genreIds = [];
        if (is_array($rawGenres)) {
            foreach ($rawGenres as $g) {
                if (is_array($g) && isset($g['id'])) {
                    $genreIds[] = (int) $g['id'];
                } elseif (is_numeric($g)) {
                    $genreIds[] = (int) $g;
                }
            }
        }
        if (empty($genreIds) && !empty($tmdbData['genre_ids']) && is_array($tmdbData['genre_ids'])) {
            $genreIds = array_map('intval', $tmdbData['genre_ids']);
        }

        $formattedGenreIds = !empty($genreIds) ? ',' . implode(',', array_unique($genreIds)) . ',' : null;
        $voteAvg = isset($tmdbData['vote_average']) && is_numeric($tmdbData['vote_average'])
            ? round((float) $tmdbData['vote_average'], 1)
            : null;

        $title = $type === 'movie' ? ($tmdbData['title'] ?? '') : ($tmdbData['name'] ?? '');
        if (empty($title)) {
            $title = $tmdbData['title'] ?? $tmdbData['name'] ?? 'Unknown';
        }

        Media::updateOrCreate(
            [
                'tmdb_id' => $id,
                'media_type' => $type,
            ],
            [
                'title' => $title,
                'poster_path' => $tmdbData['poster_path'] ?? null,
                'vote_average' => $voteAvg,
                'genre_ids' => $formattedGenreIds,
                'data' => $tmdbData,
            ]
        );

        // Invalidate catalog first-page and count caches so new addition appears immediately
        Cache::forget("media:items:{$type}:" . md5('||latest|1'));
        Cache::forget("media:count:{$type}:" . md5('|'));
        if ($formattedGenreIds && str_contains($formattedGenreIds, ',16,')) {
            Cache::forget('media:items:anime:' . md5('|||latest|1'));
            Cache::forget('media:count:anime:' . md5('||'));
        }
    }

    /**
     * Heal existing media record missing genre_ids or vote_average without re-fetching from TMDB.
     */
    protected function healMediaRecord(Media $media, array $data): void
    {
        $rawGenres = $data['genres'] ?? [];
        $genreIds = [];
        if (is_array($rawGenres)) {
            foreach ($rawGenres as $g) {
                if (is_array($g) && isset($g['id'])) {
                    $genreIds[] = (int) $g['id'];
                } elseif (is_numeric($g)) {
                    $genreIds[] = (int) $g;
                }
            }
        }
        if (empty($genreIds) && !empty($data['genre_ids']) && is_array($data['genre_ids'])) {
            $genreIds = array_map('intval', $data['genre_ids']);
        }

        $formattedGenreIds = !empty($genreIds) ? ',' . implode(',', array_unique($genreIds)) . ',' : null;
        $voteAvg = isset($data['vote_average']) && is_numeric($data['vote_average'])
            ? round((float) $data['vote_average'], 1)
            : null;

        $updates = [];
        if ($media->genre_ids === null && $formattedGenreIds !== null) {
            $updates['genre_ids'] = $formattedGenreIds;
        }
        if ($media->vote_average === null && $voteAvg !== null) {
            $updates['vote_average'] = $voteAvg;
        }

        if (!empty($updates)) {
            $media->update($updates);
        }
    }

    public function watch($type, $id)
    {
        if (!in_array($type, ['movie', 'tv'], true)) {
            abort(404);
        }

        $inWatchlist = false;
        if (Auth::check()) {
            $inWatchlist = Watchlist::where('user_id', Auth::id())
                ->where('media_id', $id)
                ->where('media_type', $type)
                ->exists();
        }

        $comments = MediaComment::with(['user', 'replies.user', 'likes', 'replies.likes'])
            ->where('media_id', $id)
            ->where('media_type', $type)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('details.show', [
            'media' => $this->fetchMedia($type, $id),
            'type' => $type,
            'inWatchlist' => $inWatchlist,
            'comments' => $comments,
        ]);
    }
}
