<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    private function getMovieGenres()
    {
        return Cache::remember('tmdb:genres:movie', now()->addHours(24), function () {
            $genresArray = Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/genre/movie/list')
                ->json('genres') ?? [];
            
            return collect($genresArray)->mapWithKeys(function($genre) {
                return [$genre['id'] => $genre['name']];
            });
        });
    }

    private function getTvGenres()
    {
        return Cache::remember('tmdb:genres:tv', now()->addHours(24), function () {
            $genresTvShow = Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/genre/tv/list')
                ->json('genres') ?? [];
            
            return collect($genresTvShow)->mapWithKeys(function($genre) {
                return [$genre['id'] => $genre['name']];
            });
        });
    }

    function index(){
        $genreId = 16;

        $popularMovies = Cache::remember('tmdb:popular_movies', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/movie/popular')
                ->json('results') ?? [];
        });
        
        $nowPlaying = Cache::remember('tmdb:now_playing', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/movie/now_playing')
                ->json('results') ?? [];
        });
        
        $topRated = Cache::remember('tmdb:top_rated', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/movie/top_rated')
                ->json('results') ?? [];
        });
            
        $newtv = Cache::remember('tmdb:new_tv', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/tv/popular')
                ->json('results') ?? [];
        });
        
        $anime = Cache::remember('tmdb:anime', now()->addHours(4), function () use ($genreId) {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/discover/movie', [
                    'with_genres' => $genreId,
                ])
                ->json('results') ?? [];
        });

        $random = Cache::remember('tmdb:random', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/movie')
                ->json('results') ?? [];
        });

        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();

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
        
        $top = Cache::remember('tmdb:tv_top_rated', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/tv/top_rated')
                ->json('results') ?? [];
        });
    
        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();
    
        return view('tvshow',[
            'top' => $top,
            'genresTv' => $genresTv,
            'genres' => $genres,
        ]);
    }

    function animation(){
        $genreId = 16;

        $popularMovies = Cache::remember('tmdb:popular_movies', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/movie/popular')
                ->json('results') ?? [];
        });

        $topAn = Cache::remember('tmdb:tv_top_anime', now()->addHours(4), function () use ($genreId) {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/discover/tv', [
                    'sort_by' => 'vote_count.desc', // Sort by top-rated
                    'with_genres' => $genreId,
                ])
                ->json('results') ?? [];
        });
        
        
        $newtv = Cache::remember('tmdb:tv_airing_today', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/tv/airing_today')
                ->json('results') ?? [];
        });
        
        $anime = Cache::remember('tmdb:anime', now()->addHours(4), function () use ($genreId) {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/discover/movie', [
                    'with_genres' => $genreId,
                ])
                ->json('results') ?? [];
        });
    
        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();
    
        
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

        $topRated = Cache::remember('tmdb:top_rated', now()->addHours(4), function () {
            return Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/movie/top_rated')
                ->json('results') ?? [];
        });
    
        $genres = $this->getMovieGenres();
    
        return view('movieList.movie',[
            'topRated' => $topRated,
            'genres' => $genres,
        ]);
    }
}