<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ShowController extends Controller
{
    function show($id){
        $popularMovies = Cache::remember('tmdb:movie:'.$id, now()->addHours(12), function () use ($id) {
            return Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/movie/'.$id.'?append_to_response=credits,videos,images')
            ->json();
        });

        $random = Cache::remember('tmdb:movie:'.$id, now()->addHours(12), function () use ($id) {
            return Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/movie/'.$id.'?append_to_response=credits,videos,images')
            ->json();
        });

        return view('movies.show',[
            'popularMovies' => $popularMovies,
            'random'=> $random,
        ]);
    }

    function movieshow($id){
        $topRated = Cache::remember('tmdb:movie:'.$id, now()->addHours(12), function () use ($id) {
            return Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/movie/'.$id.'?append_to_response=credits,videos,images')
            ->json();
        });

        return view('movieList.movieshow',[
            'topRated' => $topRated,
        ]);        
    }

    function showtv($id){
        $newtv = Cache::remember('tmdb:tv:'.$id, now()->addHours(12), function () use ($id) {
            return Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/tv/'.$id.'?append_to_response=credits,videos,images')
            ->json();
        });

        return view('tvshow.show',[
            'newtv' => $newtv,
        ]);
    }

    function showdetailtv($id){
        $top = Cache::remember('tmdb:tv:'.$id, now()->addHours(12), function () use ($id) {
            return Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/tv/'.$id.'?append_to_response=credits,videos,images')
            ->json();
        });

        return view('showdetailtv',[
            'top' => $top,
        ]);
    }

    function showani($id){
        $genreId = 16;
        $anime = Cache::remember('tmdb:anime:'.$id, now()->addHours(12), function () use ($id, $genreId) {
            return Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/discover/movie/'.$id.'?append_to_response=credits,videos,images', [
                'with_genres' => $genreId,
            ])
            ->json('results');
        });

        return view('Animation.showAni',[
            'anime' => $anime,
        ]);
    }

    function showanime($id){
        $genreId = 16;
        $anime = Cache::remember('tmdb:anime:'.$id, now()->addHours(12), function () use ($id, $genreId) {
            return Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/discover/movie/'.$id.'?append_to_response=credits,videos,images', [
                'with_genres' => $genreId,
            ])
            ->json('results');
        });

        return view('Animation.showAni',[
            'anime' => $anime,
        ]);
    }
}
