<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Safely fetch data from TMDB API with timeout and retry.
     * Returns null on failure so failed responses are not cached long-term.
     */
    private function fetchTmdb(string $endpoint, array $query = [], string $key = 'results'): ?array
    {
        try {
            $token = config('services.tmdb.token');
            if (empty($token)) {
                return null;
            }

            $response = Http::withToken($token)
                ->timeout(5)
                ->retry(2, 200)
                ->get($endpoint, $query);

            if ($response->successful()) {
                return $response->json($key) ?? [];
            }

            Log::warning("TMDB request failed for {$endpoint} with status {$response->status()}");
        } catch (\Throwable $e) {
            Log::warning("TMDB request exception for {$endpoint}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch from TMDB and cache only on successful response.
     */
    private function rememberTmdb(string $cacheKey, string $endpoint, array $query = [], string $resultsKey = 'results', int $ttlHours = 4): array
    {
        if (Cache::has($cacheKey)) {
            return (array) Cache::get($cacheKey);
        }

        $data = $this->fetchTmdb($endpoint, $query, $resultsKey);

        if ($data !== null) {
            Cache::put($cacheKey, $data, now()->addHours($ttlHours));
            return $data;
        }

        // Return empty array for this request without polluting cache with an outage result
        return [];
    }

    private function getMovieGenres()
    {
        $genres = $this->rememberTmdb('tmdb:genres:movie', 'https://api.themoviedb.org/3/genre/movie/list', [], 'genres', 24);
        return collect($genres)->mapWithKeys(function($genre) {
            return [$genre['id'] => $genre['name']];
        });
    }

    private function getTvGenres()
    {
        $genres = $this->rememberTmdb('tmdb:genres:tv', 'https://api.themoviedb.org/3/genre/tv/list', [], 'genres', 24);
        return collect($genres)->mapWithKeys(function($genre) {
            return [$genre['id'] => $genre['name']];
        });
    }

    public function index()
    {
        $genreId = 16;

        $popularMovies = $this->rememberTmdb('tmdb:popular_movies', 'https://api.themoviedb.org/3/movie/popular');
        $nowPlaying = $this->rememberTmdb('tmdb:now_playing', 'https://api.themoviedb.org/3/movie/now_playing');
        $topRated = $this->rememberTmdb('tmdb:top_rated', 'https://api.themoviedb.org/3/movie/top_rated');
        $newtv = $this->rememberTmdb('tmdb:new_tv', 'https://api.themoviedb.org/3/tv/popular');
        $anime = $this->rememberTmdb('tmdb:anime', 'https://api.themoviedb.org/3/discover/movie', ['with_genres' => $genreId]);
        $random = $this->rememberTmdb('tmdb:random', 'https://api.themoviedb.org/3/movie');

        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();

        return view('movies.index', [
            'popularMovies' => $popularMovies,
            'nowPlaying' => $nowPlaying,
            'topRated' => $topRated,
            'random' => $random,
            'newtv' => $newtv,
            'anime' => $anime,
            'genresTv' => $genresTv,
            'genres' => $genres,
        ]);
    }

    public function tvshow()
    {
        $top = $this->rememberTmdb('tmdb:tv_top_rated', 'https://api.themoviedb.org/3/tv/top_rated');

        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();

        return view('tvshow', [
            'top' => $top,
            'genresTv' => $genresTv,
            'genres' => $genres,
        ]);
    }

    public function animation()
    {
        $genreId = 16;

        $popularMovies = $this->rememberTmdb('tmdb:popular_movies', 'https://api.themoviedb.org/3/movie/popular');
        $topAn = $this->rememberTmdb('tmdb:tv_top_anime', 'https://api.themoviedb.org/3/discover/tv', [
            'sort_by' => 'vote_count.desc',
            'with_genres' => $genreId,
        ]);
        $newtv = $this->rememberTmdb('tmdb:tv_airing_today', 'https://api.themoviedb.org/3/tv/airing_today');
        $anime = $this->rememberTmdb('tmdb:anime', 'https://api.themoviedb.org/3/discover/movie', ['with_genres' => $genreId]);

        $genres = $this->getMovieGenres();
        $genresTv = $this->getTvGenres();

        return view('Animation.animation', [
            'popularMovies' => $popularMovies,
            'newtv' => $newtv,
            'anime' => $anime,
            'genresTv' => $genresTv,
            'genres' => $genres,
            'topAn' => $topAn,
        ]);
    }

    public function movieList()
    {
        $topRated = $this->rememberTmdb('tmdb:top_rated', 'https://api.themoviedb.org/3/movie/top_rated');
        $genres = $this->getMovieGenres();

        return view('movieList.movie', [
            'topRated' => $topRated,
            'genres' => $genres,
        ]);
    }
}