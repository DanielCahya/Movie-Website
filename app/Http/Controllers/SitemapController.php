<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Media;
use App\Models\Thread;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // Cache the sitemap XML for 1 hour to prevent redundant DB queries during search engine crawls
        $xml = Cache::remember('sitemap:xml:v1', now()->addHour(), function () {
            $urls = [];

            // 1. Static Core Pages
            $staticRoutes = [
                ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => date('Y-m-d')],
                ['loc' => url('/home'), 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => date('Y-m-d')],
                ['loc' => url('/tvshow'), 'priority' => '0.8', 'changefreq' => 'daily', 'lastmod' => date('Y-m-d')],
                ['loc' => url('/movieList'), 'priority' => '0.8', 'changefreq' => 'daily', 'lastmod' => date('Y-m-d')],
                ['loc' => url('/animation'), 'priority' => '0.8', 'changefreq' => 'daily', 'lastmod' => date('Y-m-d')],
                ['loc' => route('forum.index'), 'priority' => '0.8', 'changefreq' => 'daily', 'lastmod' => date('Y-m-d')],
                ['loc' => route('privacy'), 'priority' => '0.3', 'changefreq' => 'monthly', 'lastmod' => null],
                ['loc' => route('terms'), 'priority' => '0.3', 'changefreq' => 'monthly', 'lastmod' => null],
            ];

            foreach ($staticRoutes as $route) {
                $urls[] = $route;
            }

            // 2. Dynamic Media Pages (Movies & TV) loaded strictly from local database (0 TMDB calls)
            $mediaList = Media::select('tmdb_id', 'media_type', 'updated_at')
                ->latest('updated_at')
                ->take(1000)
                ->get();

            foreach ($mediaList as $media) {
                $urls[] = [
                    'loc' => route('watch', ['type' => $media->media_type, 'id' => $media->tmdb_id]),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod' => $media->updated_at ? $media->updated_at->toAtomString() : null,
                ];
            }

            // 3. Dynamic Forum Categories
            $categories = Category::select('slug', 'updated_at')->get();
            foreach ($categories as $category) {
                $urls[] = [
                    'loc' => route('forum.category', ['slug' => $category->slug]),
                    'priority' => '0.6',
                    'changefreq' => 'daily',
                    'lastmod' => $category->updated_at ? $category->updated_at->toAtomString() : null,
                ];
            }

            // 4. Dynamic Forum Public Threads
            $threads = Thread::select('slug', 'updated_at')->latest('updated_at')->take(200)->get();
            foreach ($threads as $thread) {
                $urls[] = [
                    'loc' => route('forum.thread', ['slug' => $thread->slug]),
                    'priority' => '0.6',
                    'changefreq' => 'daily',
                    'lastmod' => $thread->updated_at ? $thread->updated_at->toAtomString() : null,
                ];
            }

            // Build clean XML document
            $doc = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $doc .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($urls as $entry) {
                $doc .= "    <url>\n";
                $doc .= '        <loc>' . htmlspecialchars($entry['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
                if (!empty($entry['lastmod'])) {
                    $doc .= '        <lastmod>' . htmlspecialchars($entry['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
                }
                $doc .= '        <changefreq>' . htmlspecialchars($entry['changefreq'], ENT_XML1, 'UTF-8') . "</changefreq>\n";
                $doc .= '        <priority>' . htmlspecialchars($entry['priority'], ENT_XML1, 'UTF-8') . "</priority>\n";
                $doc .= "    </url>\n";
            }

            $doc .= '</urlset>';

            return $doc;
        });

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
