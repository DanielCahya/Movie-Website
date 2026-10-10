<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [
            url('/'),
            url('/home'),
            url('/tvshow'),
            url('/movieList'),
            url('/animation'),
            url('/forum'),
            url('/login'),
        ];

        // Fetch some dynamic top movies for the sitemap to give Google a start
        try {
            $movies = Http::withToken(config('services.tmdb.token'))
                ->get('https://api.themoviedb.org/3/movie/popular')
                ->json();
            if (isset($movies['results'])) {
                foreach (array_slice($movies['results'], 0, 50) as $movie) {
                    $urls[] = route('watch', ['type' => 'movie', 'id' => $movie['id']]);
                }
            }
        } catch (\Exception $e) {
            // Silently continue if API fails
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url) . '</loc>';
            $xml .= '<changefreq>daily</changefreq>';
            $xml .= '<priority>' . ($url == url('/') ? '1.0' : '0.8') . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'text/xml');
    }
}
