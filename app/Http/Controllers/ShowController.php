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
            return is_string($localMedia->data) ? json_decode($localMedia->data, true) : $localMedia->data;
        }

        // 2. If not in DB, fetch from TMDB with timeout & retry
        $token = config('services.tmdb.token');
        if (empty($token)) {
            return [];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(5)
                ->retry(2, 200)
                ->get("https://api.themoviedb.org/3/{$type}/{$id}?append_to_response=credits,videos,images,similar");

            if ($response->successful()) {
                $tmdbData = $response->json();

                // 3. Save to local database
                if (!isset($tmdbData['success']) || $tmdbData['success'] !== false) {
                    \App\Models\Media::create([
                        'tmdb_id' => $id,
                        'media_type' => $type,
                        'title' => $type === 'movie' ? ($tmdbData['title'] ?? '') : ($tmdbData['name'] ?? ''),
                        'poster_path' => $tmdbData['poster_path'] ?? null,
                        'data' => $tmdbData,
                    ]);
                }

                return $tmdbData;
            }

            Log::warning("TMDB fetchMedia returned HTTP {$response->status()} for {$type}/{$id}");
        } catch (\Throwable $e) {
            Log::warning("TMDB fetchMedia exception for {$type}/{$id}: " . $e->getMessage());
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
