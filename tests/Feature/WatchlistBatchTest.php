<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WatchlistBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_watchlist_loads_and_batch_hydrates_media_in_one_query_without_tmdb()
    {
        Http::fake();

        $user = User::create([
            'email' => 'watchlist@example.com',
            'username' => 'watchuser',
            'password' => bcrypt('password123'),
        ]);

        // Seed 3 media records in local MySQL
        Media::create([
            'tmdb_id' => 101,
            'media_type' => 'movie',
            'title' => 'Inception',
            'poster_path' => '/inception.jpg',
            'data' => [
                'id' => 101,
                'title' => 'Inception',
                'poster_path' => '/inception.jpg',
                'release_date' => '2010-07-16',
                'vote_average' => 8.8,
            ]
        ]);

        Media::create([
            'tmdb_id' => 102,
            'media_type' => 'movie',
            'title' => 'Interstellar',
            'poster_path' => '/interstellar.jpg',
            'data' => [
                'id' => 102,
                'title' => 'Interstellar',
                'poster_path' => '/interstellar.jpg',
                'release_date' => '2014-11-07',
                'vote_average' => 8.6,
            ]
        ]);

        Media::create([
            'tmdb_id' => 201,
            'media_type' => 'tv',
            'title' => 'Breaking Bad',
            'poster_path' => '/breakingbad.jpg',
            'data' => [
                'id' => 201,
                'name' => 'Breaking Bad',
                'poster_path' => '/breakingbad.jpg',
                'first_air_date' => '2008-01-20',
                'vote_average' => 9.5,
            ]
        ]);

        // Seed user's watchlist with these 3 items
        Watchlist::create(['user_id' => $user->id, 'media_id' => 101, 'media_type' => 'movie', 'created_at' => now()->subMinutes(3)]);
        Watchlist::create(['user_id' => $user->id, 'media_id' => 102, 'media_type' => 'movie', 'created_at' => now()->subMinutes(2)]);
        Watchlist::create(['user_id' => $user->id, 'media_id' => 201, 'media_type' => 'tv', 'created_at' => now()->subMinute()]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($user)->get('/profile/watchlist');

        $response->assertStatus(200);
        $response->assertSee('Inception');
        $response->assertSee('Interstellar');
        $response->assertSee('Breaking Bad');
        $response->assertSee('https://image.tmdb.org/t/p/w500/inception.jpg');

        // Zero TMDB HTTP calls made
        Http::assertNothingSent();

        // Check query log: exactly one query on the media table
        $mediaQueries = collect(DB::getQueryLog())->filter(function ($query) {
            return str_contains(strtolower($query['query']), 'from "media"') || str_contains(strtolower($query['query']), 'from `media`');
        });

        // Ensure exactly 1 batch query was made on the media table (NOT 3 separate queries!)
        $this->assertCount(1, $mediaQueries);
    }

    public function test_watchlist_falls_back_and_persists_missing_media()
    {
        $user = User::create([
            'email' => 'fallback@example.com',
            'username' => 'fallbackuser',
            'password' => bcrypt('password123'),
        ]);

        // Item 505 is in watchlist but NOT in media table
        Watchlist::create(['user_id' => $user->id, 'media_id' => 505, 'media_type' => 'movie']);

        config(['services.tmdb.token' => 'mock-token']);

        Http::fake([
            'https://api.themoviedb.org/3/movie/505*' => Http::response([
                'id' => 505,
                'title' => 'Fallback Movie',
                'poster_path' => '/fallback.jpg',
                'overview' => 'Fetched on demand',
                'release_date' => '2023-05-01',
                'vote_average' => 7.2,
                'videos' => ['results' => []],
                'credits' => ['cast' => []],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get('/profile/watchlist');

        $response->assertStatus(200);
        $response->assertSee('Fallback Movie');

        // Verify the item is now saved into local media table
        $persisted = Media::where('tmdb_id', 505)->where('media_type', 'movie')->first();
        $this->assertNotNull($persisted);
        $this->assertEquals('Fallback Movie', $persisted->title);
    }

    public function test_empty_watchlist_renders_friendly_state_without_media_queries()
    {
        Http::fake();

        $user = User::create([
            'email' => 'empty@example.com',
            'username' => 'emptyuser',
            'password' => bcrypt('password123'),
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($user)->get('/profile/watchlist');

        $response->assertStatus(200);
        $response->assertSee('Your Watchlist is empty');

        Http::assertNothingSent();

        // 0 media queries executed
        $mediaQueries = collect(DB::getQueryLog())->filter(function ($query) {
            return str_contains(strtolower($query['query']), 'from "media"') || str_contains(strtolower($query['query']), 'from `media`');
        });
        $this->assertCount(0, $mediaQueries);
    }
}

