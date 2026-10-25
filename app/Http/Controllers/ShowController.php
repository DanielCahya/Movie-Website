<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShowController extends Controller
{
    public function fetchMedia($type, $id)
    {
        // 1. Try to fetch from local MySQL database
        $localMedia = \App\Models\Media::where('tmdb_id', $id)->where('media_type', $type)->first();

        if ($localMedia && $localMedia->data) {
            $data = is_string($localMedia->data) ? json_decode($localMedia->data, true) : $localMedia->data;
            // Verify if local record contains rich details (videos or credits) AND is fresh (within 7 days)
            $hasRichDetails = !empty($data['videos']) || !empty($data['credits']);
            $isFresh = $localMedia->updated_at && $localMedia->updated_at->gt(now()->subDays(7));

            if ($hasRichDetails && $isFresh) {
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
                    \App\Models\Media::updateOrCreate(
                        [
                            'tmdb_id' => $id,
                            'media_type' => $type,
                        ],
                        [
                            'title' => $type === 'movie' ? ($tmdbData['title'] ?? '') : ($tmdbData['name'] ?? ''),
                            'poster_path' => $tmdbData['poster_path'] ?? null,
                            'data' => $tmdbData,
                        ]
                    );
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

    public function watch($type, $id)
    {
        if (!in_array($type, ['movie', 'tv'], true)) {
            abort(404);
        }

        $inWatchlist = false;
        if (\Illuminate\Support\Facades\Auth::check()) {
            $inWatchlist = \App\Models\Watchlist::where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->where('media_id', $id)
                ->where('media_type', $type)
                ->exists();
        }

        $comments = \App\Models\MediaComment::with(['user', 'replies.user', 'likes', 'replies.likes'])
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
