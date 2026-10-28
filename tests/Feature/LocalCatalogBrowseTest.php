<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocalCatalogBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_list_serves_from_local_database_without_tmdb_calls()
    {
        Http::fake();

        // Seed 5 movies in local database
        for ($i = 1; $i <= 5; $i++) {
            Media::create([
                'tmdb_id' => 100 + $i,
                'media_type' => 'movie',
                'title' => "Local Test Movie {$i}",
                'poster_path' => "/poster{$i}.jpg",
                'data' => [
                    'id' => 100 + $i,
                    'title' => "Local Test Movie {$i}",
                    'vote_average' => 7.5,
                    'genre_ids' => [28, 12],
                ],
            ]);
        }

        $response = $this->get('/movieList');

        $response->assertStatus(200);
        $response->assertSee('All Movies');
        $response->assertSee('Local Test Movie 1');
        $response->assertSee('Local Test Movie 5');

        // Zero external TMDB HTTP calls should be made when local database contains movies
        Http::assertNothingSent();
    }

    public function test_movie_list_filters_by_search_keyword()
    {
        Http::fake();

        Media::create([
            'tmdb_id' => 101,
            'media_type' => 'movie',
            'title' => 'Inception',
            'poster_path' => '/inception.jpg',
            'data' => ['id' => 101, 'title' => 'Inception'],
        ]);

        Media::create([
            'tmdb_id' => 102,
            'media_type' => 'movie',
            'title' => 'Interstellar',
            'poster_path' => '/interstellar.jpg',
            'data' => ['id' => 102, 'title' => 'Interstellar'],
        ]);

        Media::create([
            'tmdb_id' => 103,
            'media_type' => 'movie',
            'title' => 'The Dark Knight',
            'poster_path' => '/dark_knight.jpg',
            'data' => ['id' => 103, 'title' => 'The Dark Knight'],
        ]);

        $response = $this->get('/movieList?q=Incep');

        $response->assertStatus(200);
        $response->assertSee('Inception');
        $response->assertDontSee('Interstellar');
        $response->assertDontSee('The Dark Knight');
    }

    public function test_movie_list_filters_by_genre_id()
    {
        Http::fake();

        // Movie with Action (28)
        Media::create([
            'tmdb_id' => 201,
            'media_type' => 'movie',
            'title' => 'Action Thriller Movie',
            'poster_path' => '/action.jpg',
            'data' => [
                'id' => 201,
                'title' => 'Action Thriller Movie',
                'genre_ids' => [28, 53],
            ],
        ]);

        // Movie with Comedy (35)
        Media::create([
            'tmdb_id' => 202,
            'media_type' => 'movie',
            'title' => 'Comedy Standup Movie',
            'poster_path' => '/comedy.jpg',
            'data' => [
                'id' => 202,
                'title' => 'Comedy Standup Movie',
                'genre_ids' => [35],
            ],
        ]);

        $response = $this->get('/movieList?genre=28');

        $response->assertStatus(200);
        $response->assertSee('Action Thriller Movie');
        $response->assertDontSee('Comedy Standup Movie');
    }

    public function test_movie_list_sorts_correctly()
    {
        Http::fake();

        Media::create([
            'tmdb_id' => 301,
            'media_type' => 'movie',
            'title' => 'Alpha Movie',
            'poster_path' => '/alpha.jpg',
            'data' => ['id' => 301, 'title' => 'Alpha Movie', 'vote_average' => 6.0],
        ]);

        Media::create([
            'tmdb_id' => 302,
            'media_type' => 'movie',
            'title' => 'Beta Movie',
            'poster_path' => '/beta.jpg',
            'data' => ['id' => 302, 'title' => 'Beta Movie', 'vote_average' => 9.5],
        ]);

        Media::create([
            'tmdb_id' => 303,
            'media_type' => 'movie',
            'title' => 'Zeta Movie',
            'poster_path' => '/zeta.jpg',
            'data' => ['id' => 303, 'title' => 'Zeta Movie', 'vote_average' => 8.0],
        ]);

        $resAsc = $this->get('/movieList?sort=title_asc');
        $resAsc->assertStatus(200);
        $resAsc->assertSeeInOrder(['Alpha Movie', 'Beta Movie', 'Zeta Movie']);

        $resDesc = $this->get('/movieList?sort=title_desc');
        $resDesc->assertStatus(200);
        $resDesc->assertSeeInOrder(['Zeta Movie', 'Beta Movie', 'Alpha Movie']);

        $resRating = $this->get('/movieList?sort=rating');
        $resRating->assertStatus(200);
        $resRating->assertSeeInOrder(['Beta Movie', 'Zeta Movie', 'Alpha Movie']);
    }

    public function test_movie_list_paginates_local_catalog()
    {
        Http::fake();

        // Seed 30 movies (page size is 24)
        for ($i = 1; $i <= 30; $i++) {
            $padded = sprintf('%02d', $i);
            Media::create([
                'tmdb_id' => 400 + $i,
                'media_type' => 'movie',
                'title' => "Catalog Item {$padded}",
                'poster_path' => "/item{$padded}.jpg",
                'data' => ['id' => 400 + $i, 'title' => "Catalog Item {$padded}"],
            ]);
        }

        $page1 = $this->get('/movieList?page=1');
        $page1->assertStatus(200);
        $page1->assertSee('Catalog Item 30'); // Newest first

        $page2 = $this->get('/movieList?page=2');
        $page2->assertStatus(200);
        $page2->assertSee('Catalog Item 01'); // Oldest on page 2
    }

    public function test_tvshow_serves_from_local_database_with_filters()
    {
        Http::fake();

        Media::create([
            'tmdb_id' => 501,
            'media_type' => 'tv',
            'title' => 'Breaking Bad',
            'poster_path' => '/bb.jpg',
            'data' => [
                'id' => 501,
                'name' => 'Breaking Bad',
                'genre_ids' => [18, 80],
                'vote_average' => 9.5,
            ],
        ]);

        Media::create([
            'tmdb_id' => 502,
            'media_type' => 'tv',
            'title' => 'Stranger Things',
            'poster_path' => '/st.jpg',
            'data' => [
                'id' => 502,
                'name' => 'Stranger Things',
                'genre_ids' => [10765, 9648],
                'vote_average' => 8.6,
            ],
        ]);

        $response = $this->get('/tvshow');
        $response->assertStatus(200);
        $response->assertSee('All TV Shows');
        $response->assertSee('Breaking Bad');
        $response->assertSee('Stranger Things');
        Http::assertNothingSent();

        // Filter by TV title
        $searchRes = $this->get('/tvshow?q=Stranger');
        $searchRes->assertStatus(200);
        $searchRes->assertSee('Stranger Things');
        $searchRes->assertDontSee('Breaking Bad');
    }

    public function test_empty_catalog_or_no_search_results_shows_empty_state_gracefully()
    {
        Http::fake();

        Media::create([
            'tmdb_id' => 601,
            'media_type' => 'movie',
            'title' => 'Existing Film',
            'poster_path' => '/film.jpg',
            'data' => ['id' => 601, 'title' => 'Existing Film'],
        ]);

        $response = $this->get('/movieList?q=NoSuchFilmEverExists');
        $response->assertStatus(200);
        $response->assertSee('No movies found');
        $response->assertSee('Clear All Filters');
    }

    public function test_tvshow_handles_nested_genre_arrays_without_htmlspecialchars_type_error()
    {
        Http::fake();

        // Simulate Redis or cache holding unflattened genre arrays from TMDB API
        Cache::put('tmdb:genres:tv', [
            0 => ['id' => 10759, 'name' => 'Action & Adventure'],
            1 => ['id' => 16, 'name' => 'Animation'],
            2 => ['id' => 35, 'name' => 'Comedy'],
        ], now()->addHours(1));

        Media::create([
            'tmdb_id' => 701,
            'media_type' => 'tv',
            'title' => 'Action Series Example',
            'poster_path' => '/action_tv.jpg',
            'data' => ['id' => 701, 'name' => 'Action Series Example', 'genre_ids' => [10759]],
        ]);

        $response = $this->get('/tvshow');
        $response->assertStatus(200);
        $response->assertSee('Action &amp; Adventure', false);
        $response->assertSee('Action Series Example');
    }

    public function test_movie_list_handles_nested_genre_arrays_without_htmlspecialchars_type_error()
    {
        Http::fake();

        // Simulate Redis or cache holding unflattened genre arrays from TMDB API
        Cache::put('tmdb:genres:movie', [
            0 => ['id' => 28, 'name' => 'Action Film Genre'],
            1 => ['id' => 12, 'name' => 'Adventure Film Genre'],
        ], now()->addHours(1));

        Media::create([
            'tmdb_id' => 801,
            'media_type' => 'movie',
            'title' => 'Adventure Movie Example',
            'poster_path' => '/adv_movie.jpg',
            'data' => ['id' => 801, 'title' => 'Adventure Movie Example', 'genre_ids' => [12]],
        ]);

        $response = $this->get('/movieList');
        $response->assertStatus(200);
        $response->assertSee('Adventure Film Genre');
        $response->assertSee('Adventure Movie Example');
    }

    public function test_animation_serves_from_local_database_with_baseline_genre_16_enforced()
    {
        Http::fake();

        // Anime Movie (genre 16)
        Media::create([
            'tmdb_id' => 901,
            'media_type' => 'movie',
            'title' => 'Spirited Away',
            'poster_path' => '/spirited.jpg',
            'data' => ['id' => 901, 'title' => 'Spirited Away', 'genre_ids' => [16, 14], 'vote_average' => 8.5],
        ]);

        // Anime TV Series (genre 16)
        Media::create([
            'tmdb_id' => 902,
            'media_type' => 'tv',
            'title' => 'Attack on Titan',
            'poster_path' => '/aot.jpg',
            'data' => ['id' => 902, 'name' => 'Attack on Titan', 'genre_ids' => [16, 10759], 'vote_average' => 9.0],
        ]);

        // Live Action Movie (NO genre 16)
        Media::create([
            'tmdb_id' => 903,
            'media_type' => 'movie',
            'title' => 'Die Hard Non-Anime',
            'poster_path' => '/die_hard.jpg',
            'data' => ['id' => 903, 'title' => 'Die Hard Non-Anime', 'genre_ids' => [28, 53], 'vote_average' => 8.2],
        ]);

        $response = $this->get('/animation');
        $response->assertStatus(200);
        $response->assertSee('Spirited Away');
        $response->assertSee('Attack on Titan');
        $response->assertDontSee('Die Hard Non-Anime');
    }

    public function test_animation_filters_by_format_selector()
    {
        Http::fake();

        Media::create([
            'tmdb_id' => 911,
            'media_type' => 'movie',
            'title' => 'Your Name Movie',
            'poster_path' => '/your_name.jpg',
            'data' => ['id' => 911, 'title' => 'Your Name Movie', 'genre_ids' => [16], 'vote_average' => 8.8],
        ]);

        Media::create([
            'tmdb_id' => 912,
            'media_type' => 'tv',
            'title' => 'Demon Slayer Series',
            'poster_path' => '/demon_slayer.jpg',
            'data' => ['id' => 912, 'name' => 'Demon Slayer Series', 'genre_ids' => [16], 'vote_average' => 8.7],
        ]);

        $resMovie = $this->get('/animation?format=movie');
        $resMovie->assertStatus(200);
        $resMovie->assertSee('Your Name Movie');
        $resMovie->assertDontSee('Demon Slayer Series');

        $resTv = $this->get('/animation?format=tv');
        $resTv->assertStatus(200);
        $resTv->assertSee('Demon Slayer Series');
        $resTv->assertDontSee('Your Name Movie');
    }

    public function test_animation_filters_by_additional_genre_alongside_genre_16()
    {
        Http::fake();

        // Anime Fantasy
        Media::create([
            'tmdb_id' => 921,
            'media_type' => 'movie',
            'title' => 'Fantasy Anime Gem',
            'poster_path' => '/fantasy.jpg',
            'data' => ['id' => 921, 'title' => 'Fantasy Anime Gem', 'genre_ids' => [16, 14], 'vote_average' => 8.0],
        ]);

        // Anime Comedy
        Media::create([
            'tmdb_id' => 922,
            'media_type' => 'tv',
            'title' => 'Comedy Anime Sitcom',
            'poster_path' => '/comedy_anime.jpg',
            'data' => ['id' => 922, 'name' => 'Comedy Anime Sitcom', 'genre_ids' => [16, 35], 'vote_average' => 7.8],
        ]);

        $response = $this->get('/animation?genre=14');
        $response->assertStatus(200);
        $response->assertSee('Fantasy Anime Gem');
        $response->assertDontSee('Comedy Anime Sitcom');
    }

    public function test_animation_genre_dropdown_vanishes_animation_genre()
    {
        Http::fake();

        $response = $this->get('/animation');
        $response->assertStatus(200);

        // Option with value "16" must be vanished from the genre dropdown
        $response->assertDontSee('<option value="16"', false);
    }
}
