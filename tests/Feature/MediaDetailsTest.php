<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Http\Controllers\ShowController;
use App\Http\Livewire\Search;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class MediaDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_details_page_renders_opengraph_and_schema_and_inline_player()
    {
        // Seed mock media record so ShowController does not call external TMDB API
        Media::create([
            'tmdb_id' => 9999,
            'media_type' => 'movie',
            'title' => 'Inception Test',
            'poster_path' => '/poster_test.jpg',
            'data' => [
                'id' => 9999,
                'title' => 'Inception Test',
                'overview' => 'A thief who steals corporate secrets through the use of dream-sharing technology.',
                'poster_path' => '/poster_test.jpg',
                'backdrop_path' => '/backdrop_test.jpg',
                'release_date' => '2010-07-16',
                'vote_average' => 8.4,
                'vote_count' => 35000,
                'status' => 'Released',
                'runtime' => 148,
                'genres' => [
                    ['id' => 28, 'name' => 'Action'],
                    ['id' => 878, 'name' => 'Science Fiction'],
                ],
                'production_companies' => [
                    ['name' => 'Syncopy']
                ],
                'videos' => [
                    'results' => [
                        [
                            'key' => 'YoHD9XEInc0',
                            'site' => 'YouTube',
                            'type' => 'Trailer',
                            'name' => 'Official Trailer'
                        ]
                    ]
                ],
                'similar' => [
                    'results' => []
                ]
            ]
        ]);

        $response = $this->get('/watch/movie/9999');

        $response->assertStatus(200);

        // Verify Title and OpenGraph meta tags
        $response->assertSee('Inception Test - Galiwe');
        $response->assertSee('property="og:type" content="video.movie"', false);
        $response->assertSee('https://image.tmdb.org/t/p/w1280/backdrop_test.jpg');

        // Verify Schema.org JSON-LD
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type": "Movie"', false);
        $response->assertSee('"trailer"', false);
        $response->assertSee('YoHD9XEInc0');

        // Verify In-Place Cinematic Player Facade structure (no disruptive modal dialog)
        $response->assertSee('Watch Trailer');
        $response->assertSee('isPlayingTrailer');
        $response->assertSee('activeTrailerKey');
        $response->assertDontSee('role="dialog"', false);
    }

    public function test_fetch_media_uses_local_database_when_fresh_rich_data_exists()
    {
        Http::fake();

        Media::create([
            'tmdb_id' => 101,
            'media_type' => 'movie',
            'title' => 'Cached Movie',
            'poster_path' => '/cached.jpg',
            'data' => [
                'id' => 101,
                'title' => 'Cached Movie',
                'videos' => ['results' => [['key' => 'abc', 'site' => 'YouTube', 'type' => 'Trailer']]],
            ]
        ]);

        $controller = new ShowController();
        $result = $controller->fetchMedia('movie', 101);

        $this->assertEquals('Cached Movie', $result['title']);
        $this->assertEquals('abc', $result['videos']['results'][0]['key']);
        Http::assertNothingSent();
    }

    public function test_fetch_media_upgrades_search_stub_to_full_details()
    {
        // 1. Seed bare search stub without videos or credits
        Media::create([
            'tmdb_id' => 202,
            'media_type' => 'movie',
            'title' => 'Stub Movie',
            'poster_path' => '/stub.jpg',
            'data' => [
                'id' => 202,
                'title' => 'Stub Movie',
                'overview' => 'Search stub only',
            ]
        ]);

        config(['services.tmdb.token' => 'fake-token']);

        // 2. Mock TMDB API returning full rich response
        Http::fake([
            'https://api.themoviedb.org/3/movie/202*' => Http::response([
                'id' => 202,
                'title' => 'Stub Movie',
                'overview' => 'Upgraded full description',
                'poster_path' => '/full_poster.jpg',
                'videos' => [
                    'results' => [
                        ['key' => 'upgraded_key', 'site' => 'YouTube', 'type' => 'Trailer']
                    ]
                ],
                'credits' => ['cast' => []],
            ], 200),
        ]);

        $controller = new ShowController();
        $result = $controller->fetchMedia('movie', 202);

        $this->assertEquals('Upgraded full description', $result['overview']);
        $this->assertEquals('upgraded_key', $result['videos']['results'][0]['key']);

        // 3. Verify database record was updated in-place with full details
        $updatedMedia = Media::where('tmdb_id', 202)->where('media_type', 'movie')->first();
        $this->assertEquals('/full_poster.jpg', $updatedMedia->poster_path);
        $this->assertArrayHasKey('videos', $updatedMedia->data);
    }

    public function test_search_does_not_overwrite_existing_full_media_records()
    {
        // Seed full record
        Media::create([
            'tmdb_id' => 303,
            'media_type' => 'movie',
            'title' => 'Interstellar',
            'poster_path' => '/full.jpg',
            'data' => [
                'id' => 303,
                'title' => 'Interstellar',
                'videos' => ['results' => [['key' => 'full_key', 'type' => 'Trailer', 'site' => 'YouTube']]],
            ]
        ]);

        config(['services.tmdb.token' => 'fake-token']);

        // Search returns a bare stub from TMDB
        Http::fake([
            'https://api.themoviedb.org/3/search/multi*' => Http::response([
                'results' => [
                    [
                        'id' => 303,
                        'media_type' => 'movie',
                        'title' => 'Interstellar',
                        'poster_path' => '/stub_poster.jpg',
                        'overview' => 'Bare search stub',
                    ]
                ]
            ], 200),
        ]);

        // Trigger search
        Livewire::test(Search::class)
            ->set('search', 'Interstellar')
            ->assertSee('Interstellar');

        // Verify the database record STILL has the full 'videos' data and was not overwritten
        $persistedMedia = Media::where('tmdb_id', 303)->where('media_type', 'movie')->first();
        $this->assertArrayHasKey('videos', $persistedMedia->data);
        $this->assertEquals('full_key', $persistedMedia->data['videos']['results'][0]['key']);
    }
}
