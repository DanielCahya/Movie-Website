<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    function index(){
    $genreId = 16;

    $popularMovies = Http::withToken(config('services.tmdb.token'))
        ->get('https://api.themoviedb.org/3/movie/popular')
        ->json('results');
    
    $nowPlaying = Http::withToken(config('services.tmdb.token'))
        ->get('https://api.themoviedb.org/3/movie/now_playing')
        ->json('results');
    
    $topRated = Http::withToken(config('services.tmdb.token'))
        ->get('https://api.themoviedb.org/3/movie/top_rated')
        ->json('results');
        
    
    $newtv = Http::withToken(config('services.tmdb.token'))
        ->get('https://api.themoviedb.org/3/tv/popular')
        ->json('results');
    
    $anime = Http::withToken(config('services.tmdb.token'))
    ->get('https://api.themoviedb.org/3/discover/movie', [
        'with_genres' => $genreId,
    ])
    ->json('results');

    $random = Http::withToken(config('services.tmdb.token'))
    ->get('https://api.themoviedb.org/3/movie')
    ->json('results');

    $genresArray = Http::withToken(config('services.tmdb.token'))
        ->get('https://api.themoviedb.org/3/genre/movie/list')
        ->json('genres');
    $genresTvShow = Http::withToken(config('services.tmdb.token'))
        ->get('https://api.themoviedb.org/3/genre/tv/list')
        ->json('genres');
    
    $genresTv = collect($genresTvShow)->mapWithKeys(function($genresTv){
        return [$genresTv['id'] => $genresTv['name']];
    });    

    $genres = collect($genresArray)->mapWithKeys(function($genres){
        return [$genres['id'] => $genres['name']];
    });

        return view('movies.index',[
            'popularMovies' => $popularMovies,
            'nowPlaying'=> $nowPlaying,
            'topRated' => $topRated,
            'random' => $random,
            'newtv' => $newtv,
            'anime' => $anime,
            'genresTv' => $genresTv,
            'genres' => $genres,
        ]);
    }

    function tvshow(){
        
        $top = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/tv/top_rated')
            ->json('results');
    
        $genresArray = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/genre/movie/list')
            ->json('genres');
        $genresTvShow = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/genre/tv/list')
            ->json('genres');
        
        $genresTv = collect($genresTvShow)->mapWithKeys(function($genresTv){
            return [$genresTv['id'] => $genresTv['name']];
        });    
    
        $genres = collect($genresArray)->mapWithKeys(function($genres){
            return [$genres['id'] => $genres['name']];
        });
    
            return view('tvshow',[
                'top' => $top,
                'genresTv' => $genresTv,
                'genres' => $genres,
            ]);
    }

    function animation(){
        $genreId = 16;

        $popularMovies = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/movie/popular')
            ->json('results');

        $topAn = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/discover/tv', [
                'sort_by' => 'vote_count.desc', // Sort by top-rated
                'with_genres' => $genreId,
            ])
            ->json('results');
        
        
        $newtv = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/tv/airing_today')
            ->json('results');
        
        $anime = Http::withToken(config('services.tmdb.token'))
        ->get('https://api.themoviedb.org/3/discover/movie', [
            'with_genres' => $genreId,
        ])
        ->json('results');
    
        $genresArray = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/genre/movie/list')
            ->json('genres');
        $genresTvShow = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/genre/tv/list')
            ->json('genres');
        
        $genresTv = collect($genresTvShow)->mapWithKeys(function($genresTv){
            return [$genresTv['id'] => $genresTv['name']];
        });    
    
        $genres = collect($genresArray)->mapWithKeys(function($genres){
            return [$genres['id'] => $genres['name']];
        });
    
        
            return view('Animation.animation',[
                'popularMovies' => $popularMovies,
                'newtv' => $newtv,
                'anime' => $anime,
                'genresTv' => $genresTv,
                'genres' => $genres,
                'topAn' => $topAn,
            ]);
    }

    function movieList(){

        $topRated = Http::withToken(config('services.tmdb.token'))
        ->get('https://api.themoviedb.org/3/movie/top_rated')
        ->json('results');
    
        $genresArray = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/genre/movie/list')
            ->json('genres');  
    
        $genres = collect($genresArray)->mapWithKeys(function($genres){
            return [$genres['id'] => $genres['name']];
        });
    
            return view('movieList.movie',[
                'topRated' => $topRated,
                'genres' => $genres,
            ]);
    }
}