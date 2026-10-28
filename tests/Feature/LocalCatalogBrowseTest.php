<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

