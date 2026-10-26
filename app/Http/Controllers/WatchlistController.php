<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Watchlist;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $watchlists = Watchlist::where('user_id', $userId)->orderBy('created_at', 'desc')->get();

        if ($watchlists->isEmpty()) {
            return view('profile.watchlist', ['mediaItems' => []]);
        }

        // 1. Batch-query all watchlist media records from local database in a single query
        $mediaIds = $watchlists->pluck('media_id')->unique()->values()->all();
        $localMediaMap = Media::whereIn('tmdb_id', $mediaIds)
            ->get()
            ->keyBy(function ($m) {
                return $m->media_type . '_' . $m->tmdb_id;
            });

        $mediaItems = [];
        $showController = null;

        // 2. Hydrate each watchlist item in memory preserving user's saved order
        foreach ($watchlists as $item) {
            $key = $item->media_type . '_' . $item->media_id;
            
            if ($localMediaMap->has($key)) {
                $local = $localMediaMap->get($key);
                $data = is_string($local->data) ? json_decode($local->data, true) : ($local->data ?? []);
                
                // Ensure required view fields exist
                $data['id'] = $local->tmdb_id;
                $data['media_type_custom'] = $local->media_type;
                if (!isset($data['title']) && !isset($data['name'])) {
                    $data['title'] = $local->title;
                }
                if (!isset($data['poster_path']) && $local->poster_path) {
                    $data['poster_path'] = $local->poster_path;
                }
                $mediaItems[] = $data;
            } else {
                // Fallback for items not yet in local database (fetches once and persists)
                if ($showController === null) {
                    $showController = new \App\Http\Controllers\ShowController();
                }
                $media = $showController->fetchMedia($item->media_type, $item->media_id);
                if (!empty($media) && (!isset($media['success']) || $media['success'] !== false)) {
                    $media['media_type_custom'] = $item->media_type;
                    $mediaItems[] = $media;
                }
            }
        }

        return view('profile.watchlist', compact('mediaItems'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'media_id' => 'required|integer',
            'media_type' => 'required|string|in:movie,tv'
        ]);

        $userId = Auth::id();

        $existing = Watchlist::where('user_id', $userId)
                             ->where('media_id', $request->media_id)
                             ->where('media_type', $request->media_type)
                             ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('success', 'Removed from Watchlist');
        } else {
            Watchlist::create([
                'user_id' => $userId,
                'media_id' => $request->media_id,
                'media_type' => $request->media_type,
            ]);
            return back()->with('success', 'Added to Watchlist');
        }
    }
}
