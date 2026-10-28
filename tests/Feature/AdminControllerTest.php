<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully()
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/genre/')) {
                return Http::response(['genres' => [['id' => 28, 'name' => 'Action']]], 200);
            }
            return Http::response([
                'results' => [
                    [
                        'id' => 101,
                        'title' => 'Sample Movie',
                        'name' => 'Sample Movie',
                        'poster_path' => '/sample.jpg',
                        'backdrop_path' => '/sample_bg.jpg',
                        'vote_average' => 8.0,
                        'release_date' => '2023-01-01',
                        'genre_ids' => [28],
                        'overview' => 'Sample overview',
                    ]
                ]
            ], 200);
        });

        $response = $this->get('/');
        $response->assertStatus(200);

        $responseHome = $this->get('/home');
        $responseHome->assertStatus(200);
    }

    public function test_animation_page_renders_successfully()
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/genre/')) {
                return Http::response(['genres' => [['id' => 16, 'name' => 'Animation']]], 200);
            }
            return Http::response([
                'results' => [
                    ['id' => 201, 'name' => 'Sample Anime TV', 'poster_path' => '/anime_tv.jpg'],
                    ['id' => 202, 'title' => 'Sample Anime Movie', 'poster_path' => '/anime_movie.jpg']
                ]
            ], 200);
        });

        $response = $this->get('/animation');
        $response->assertStatus(200);
        $response->assertSee('Top Anime TV Shows');
        $response->assertSee('Top Anime Movies');
    }

    public function test_home_page_falls_back_to_local_media_when_tmdb_is_offline()
    {
        // TMDB returns 500 error for all requests
        Http::fake(fn () => Http::response(null, 500));

        // Seed local movies
        Media::create([
            'tmdb_id' => 999,
            'media_type' => 'movie',
            'title' => 'Offline Fallback Movie',
            'poster_path' => '/offline.jpg',
            'data' => [
                'id' => 999,
                'title' => 'Offline Fallback Movie',
                'backdrop_path' => '/offline_bg.jpg',
                'vote_average' => 7.8,
                'release_date' => '2022-05-01',
                'genre_ids' => [28],
                'overview' => 'Offline overview',
            ],
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Offline Fallback Movie');
    }

    public function test_movie_list_and_tv_show_render_when_cache_service_is_unreachable()
    {
        Http::fake(fn () => Http::response(['results' => []], 200));

        Media::create([
            'tmdb_id' => 888,
            'media_type' => 'movie',
            'title' => 'Resilient Movie',
            'poster_path' => '/resilient.jpg',
            'data' => ['id' => 888, 'title' => 'Resilient Movie', 'genre_ids' => [28]],
        ]);

        $response = $this->get('/movieList');
        $response->assertStatus(200);
        $response->assertSee('Resilient Movie');

        $responseTv = $this->get('/tvshow');
        $responseTv->assertStatus(200);
        $responseTv->assertSee('All TV Shows');
    }

    public function test_pages_render_gracefully_when_media_table_is_missing()
    {
        Http::fake(fn () => Http::response(['results' => []], 200));

        // Simulate unmigrated database or dropped media table
        Schema::dropIfExists('media');

        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);

        $responseAnimation = $this->get('/animation');
        $responseAnimation->assertStatus(200);

        $responseMovies = $this->get('/movieList');
        $responseMovies->assertStatus(200);

        $responseTv = $this->get('/tvshow');
        $responseTv->assertStatus(200);
    }
}

