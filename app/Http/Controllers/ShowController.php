<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ShowController extends Controller
{
    private function fetchMedia($type, $id) {
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
        return view('details.show', ['media' => $this->fetchMedia($type, $id), 'type' => $type]);
    }
}
