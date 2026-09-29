<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ShowController extends Controller
{
    public function fetchMedia($type, $id) {
        return Cache::remember("tmdb:{$type}:{$id}", now()->addHours(12), function () use ($type, $id) {
            return Http::withToken(config('services.tmdb.token'))
            ->get("https://api.themoviedb.org/3/{$type}/{$id}?append_to_response=credits,videos,images,similar")
            ->json();
        });
    }

    public function watch($type, $id) {
        if (!in_array($type, ['movie', 'tv'])) {
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
            'comments' => $comments
        ]);
    }
}
