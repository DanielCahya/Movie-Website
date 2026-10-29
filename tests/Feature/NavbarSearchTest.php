<?php

namespace Tests\Feature;

use App\Http\Livewire\Search;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class NavbarSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_navbar_search_renders_successfully()
    {
        Livewire::test(Search::class)
            ->assertStatus(200)
            ->assertDontSee('No results found for');
    }

    public function test_short_search_query_does_not_execute_searches()
    {
        Http::fake();

        Livewire::test(Search::class)
            ->set('search', 'a')
            ->assertSet('search', 'a')
            ->assertDontSee('No results found for');

        Http::assertNothingSent();
    }

    public function test_search_finds_local_media_by_prefix_and_substring()
    {
        Http::fake();

        Media::create([
            'tmdb_id' => 101,
            'media_type' => 'movie',
            'title' => 'Spider-Man: Across the Spider-Verse',
            'poster_path' => '/spider.jpg',
            'data' => ['id' => 101, 'title' => 'Spider-Man: Across the Spider-Verse'],
        ]);

        Media::create([
            'tmdb_id' => 102,
            'media_type' => 'movie',
            'title' => 'The Amazing Spider-Man',
            'poster_path' => '/amazing.jpg',
            'data' => ['id' => 102, 'title' => 'The Amazing Spider-Man'],
        ]);

        Media::create([
            'tmdb_id' => 103,
            'media_type' => 'tv',
            'title' => 'Batman Beyond',
            'poster_path' => '/batman.jpg',
            'data' => ['id' => 103, 'title' => 'Batman Beyond'],
        ]);

        // Search for 'Spider' should match both prefix and substring, but not Batman
        Livewire::test(Search::class)
            ->set('search', 'Spider')
            ->assertSee('Spider-Man: Across the Spider-Verse')
            ->assertSee('The Amazing Spider-Man')
            ->assertDontSee('Batman Beyond');

        // Zero external HTTP requests made because local database fulfilled search
        Http::assertNothingSent();
    }

    public function test_search_falls_back_to_tmdb_when_local_has_no_matches_and_stores_result()
    {
        Http::fake([
            'https://api.themoviedb.org/3/search/multi*' => Http::response([
                'page' => 1,
                'results' => [
                    [
                        'id' => 99991,
                        'media_type' => 'movie',
                        'title' => 'Oppenheimer',
                        'poster_path' => '/oppenheimer.jpg',
                        'vote_average' => 8.1,
                        'genre_ids' => [18, 36],
                    ],
                    [
                        'id' => 99992,
                        'media_type' => 'person', // Should be filtered out!
                        'name' => 'Christopher Nolan',
                    ],
                ],
            ], 200),
        ]);

        config(['services.tmdb.token' => 'fake-token']);

        $this->assertEquals(0, Media::where('tmdb_id', 99991)->count());

        Livewire::test(Search::class)
            ->set('search', 'Oppenheimer')
            ->assertSee('Oppenheimer')
            ->assertDontSee('Christopher Nolan');

        // Verify the movie was saved locally with performance columns populated
        $saved = Media::where('tmdb_id', 99991)->first();
        $this->assertNotNull($saved);
        $this->assertEquals('Oppenheimer', $saved->title);
        $this->assertEquals('movie', $saved->media_type);
        $this->assertEquals('/oppenheimer.jpg', $saved->poster_path);
        $this->assertEquals(8.1, (float) $saved->vote_average);
        $this->assertEquals(',18,36,', $saved->genre_ids);
    }

    public function test_search_handles_tmdb_failures_gracefully()
    {
        Http::fake([
            'https://api.themoviedb.org/3/search/multi*' => Http::response(null, 500),
        ]);

        config(['services.tmdb.token' => 'fake-token']);

        Livewire::test(Search::class)
            ->set('search', 'NonexistentMovie123')
            ->assertStatus(200)
            ->assertSee('No results found for')
            ->assertSee('NonexistentMovie123');
    }
}
