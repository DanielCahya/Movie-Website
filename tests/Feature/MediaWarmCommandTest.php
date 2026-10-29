<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MediaWarmCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_fails_gracefully_when_token_is_missing()
    {
        config(['services.tmdb.token' => null]);

        $this->artisan('media:warm')
            ->assertFailed();
    }

    public function test_command_fetches_and_persists_titles_from_tmdb()
    {
        config(['services.tmdb.token' => 'mock-token']);

        Http::fake([
            'https://api.themoviedb.org/3/movie/popular*' => Http::response([
                'results' => [
                    [
                        'id' => 101,
                        'title' => 'Warm Movie 1',
                        'poster_path' => '/warm1.jpg',
                        'overview' => 'Movie 1 overview',
                    ]
                ]
            ], 200),
            'https://api.themoviedb.org/3/movie/top_rated*' => Http::response([
                'results' => [
                    [
                        'id' => 102,
                        'title' => 'Warm Movie 2',
                        'poster_path' => '/warm2.jpg',
                        'overview' => 'Movie 2 overview',
                    ]
                ]
            ], 200),
            'https://api.themoviedb.org/3/tv/popular*' => Http::response([
                'results' => [
                    [
                        'id' => 201,
                        'name' => 'Warm TV 1',
                        'poster_path' => '/warmtv1.jpg',
                        'overview' => 'TV 1 overview',
                    ]
                ]
            ], 200),
            'https://api.themoviedb.org/3/tv/top_rated*' => Http::response([
                'results' => [
                    [
                        'id' => 202,
                        'name' => 'Warm TV 2',
                        'poster_path' => '/warmtv2.jpg',
                        'overview' => 'TV 2 overview',
                    ]
                ]
            ], 200),
            'https://api.themoviedb.org/3/discover/tv*' => Http::response([
                'results' => []
            ], 200),
            'https://api.themoviedb.org/3/discover/movie*' => Http::response([
                'results' => []
            ], 200),
        ]);

        $this->artisan('media:warm', ['--pages' => 1])
            ->assertSuccessful();

        // Verify records were inserted into local database
        $this->assertDatabaseHas('media', ['tmdb_id' => 101, 'media_type' => 'movie', 'title' => 'Warm Movie 1']);
        $this->assertDatabaseHas('media', ['tmdb_id' => 102, 'media_type' => 'movie', 'title' => 'Warm Movie 2']);
        $this->assertDatabaseHas('media', ['tmdb_id' => 201, 'media_type' => 'tv', 'title' => 'Warm TV 1']);
        $this->assertDatabaseHas('media', ['tmdb_id' => 202, 'media_type' => 'tv', 'title' => 'Warm TV 2']);
    }

    public function test_command_filters_by_type_option()
    {
        config(['services.tmdb.token' => 'mock-token']);

        Http::fake([
            'https://api.themoviedb.org/3/movie/popular*' => Http::response([
                'results' => [
                    ['id' => 301, 'title' => 'Filtered Movie', 'poster_path' => '/filt.jpg']
                ]
            ], 200),
            'https://api.themoviedb.org/3/movie/top_rated*' => Http::response([
                'results' => []
            ], 200),
        ]);

        $this->artisan('media:warm', ['--type' => 'movie', '--pages' => 1])
            ->assertSuccessful();

        $this->assertDatabaseHas('media', ['tmdb_id' => 301, 'media_type' => 'movie']);
        // Verify no TV shows were requested or inserted
        $this->assertEquals(0, Media::where('media_type', 'tv')->count());
    }

    public function test_command_does_not_overwrite_existing_rich_records()
    {
        config(['services.tmdb.token' => 'mock-token']);

        // Seed an existing rich record with trailers in database
        Media::create([
            'tmdb_id' => 401,
            'media_type' => 'movie',
            'title' => 'Existing Rich Movie',
            'poster_path' => '/rich.jpg',
            'data' => [
                'id' => 401,
                'title' => 'Existing Rich Movie',
                'videos' => ['results' => [['key' => 'existing_trailer_key']]],
            ]
        ]);

        // TMDB returns bare item with same ID
        Http::fake([
            'https://api.themoviedb.org/3/movie/popular*' => Http::response([
                'results' => [
                    ['id' => 401, 'title' => 'Existing Rich Movie', 'poster_path' => '/rich.jpg']
                ]
            ], 200),
            'https://api.themoviedb.org/3/movie/top_rated*' => Http::response(['results' => []], 200),
        ]);

        $this->artisan('media:warm', ['--type' => 'movie', '--pages' => 1])
            ->assertSuccessful();

        // Assert existing rich videos were preserved intact
        $record = Media::where('tmdb_id', 401)->where('media_type', 'movie')->first();
        $this->assertArrayHasKey('videos', $record->data);
        $this->assertEquals('existing_trailer_key', $record->data['videos']['results'][0]['key']);
    }

    public function test_command_warms_anime_catalog_specifically()
    {
        config(['services.tmdb.token' => 'mock-token']);

        Http::fake([
            'https://api.themoviedb.org/3/discover/tv*' => Http::response([
                'results' => [
                    ['id' => 901, 'name' => 'Demon Slayer', 'genre_ids' => [16, 10759], 'vote_average' => 8.7]
                ]
            ], 200),
            'https://api.themoviedb.org/3/discover/movie*' => Http::response([
                'results' => [
                    ['id' => 902, 'title' => 'Spirited Away', 'genre_ids' => [16, 14], 'vote_average' => 8.5]
                ]
            ], 200),
        ]);

        $this->artisan('media:warm', ['--type' => 'anime', '--pages' => 1])
            ->assertSuccessful();

        $this->assertDatabaseHas('media', ['tmdb_id' => 901, 'media_type' => 'tv', 'title' => 'Demon Slayer']);
        $this->assertDatabaseHas('media', ['tmdb_id' => 902, 'media_type' => 'movie', 'title' => 'Spirited Away']);
    }
}
