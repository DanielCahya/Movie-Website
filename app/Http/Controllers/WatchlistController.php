<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Watchlist;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $watchlists = Watchlist::where('user_id', $userId)->orderBy('created_at', 'desc')->get();

        $mediaItems = [];
        $showController = new \App\Http\Controllers\ShowController();
        foreach ($watchlists as $item) {
            $media = $showController->fetchMedia($item->media_type, $item->media_id);
            if ($media && !isset($media['success']) || (isset($media['success']) && $media['success'] !== false)) { // Basic TMDB error check
                $media['media_type_custom'] = $item->media_type;
                $mediaItems[] = $media;
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
