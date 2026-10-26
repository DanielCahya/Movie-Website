<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_renders_valid_xml_from_local_database_with_zero_tmdb_calls()
    {
        Http::fake();
        Cache::flush();

        // 1. Seed local movie and tv records
        Media::create([
            'tmdb_id' => 101,
            'media_type' => 'movie',
            'title' => 'Inception',
            'poster_path' => '/inception.jpg',
            'data' => ['id' => 101, 'title' => 'Inception'],
        ]);

        Media::create([
            'tmdb_id' => 202,
            'media_type' => 'tv',
            'title' => 'Stranger Things',
            'poster_path' => '/stranger.jpg',
            'data' => ['id' => 202, 'name' => 'Stranger Things'],
        ]);

        // 2. Seed a forum category and thread
        $user = User::create([
            'email' => 'forumauthor@example.com',
            'username' => 'forumauthor',
            'password' => bcrypt('password123'),
        ]);

        $category = Category::create([
            'name' => 'General Discussion',
            'slug' => 'general-discussion',
            'description' => 'Talk about everything',
        ]);

        Thread::create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'title' => 'Best Movies of the Decade',
            'slug' => 'best-movies-of-the-decade',
            'content' => 'What is your favorite movie?',
            'views' => 42,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');

        // Verify valid XML structure
        $response->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false);
        $response->assertSee('<loc>http://localhost/watch/movie/101</loc>', false);
        $response->assertSee('<loc>http://localhost/watch/tv/202</loc>', false);
        $response->assertSee('<loc>http://localhost/forum/category/general-discussion</loc>', false);
        $response->assertSee('<loc>http://localhost/forum/thread/best-movies-of-the-decade</loc>', false);
        $response->assertSee('<lastmod>', false);

        // Verify zero external network calls were made to TMDB
        Http::assertNothingSent();
    }

    public function test_sitemap_is_cached_and_served_efficiently()
    {
        Http::fake();
        Cache::flush();

        Media::create([
            'tmdb_id' => 999,
            'media_type' => 'movie',
            'title' => 'Cached Title',
            'poster_path' => '/cached.jpg',
            'data' => ['id' => 999, 'title' => 'Cached Title'],
        ]);

        // First call caches the output
        $response1 = $this->get('/sitemap.xml');
        $response1->assertStatus(200);
        $this->assertTrue(Cache::has('sitemap:xml:v1'));

        // Delete the record from DB to verify second call serves directly from cache
        Media::where('tmdb_id', 999)->delete();

        $response2 = $this->get('/sitemap.xml');
        $response2->assertStatus(200);
        $response2->assertSee('<loc>http://localhost/watch/movie/999</loc>', false);

        Http::assertNothingSent();
    }
}

