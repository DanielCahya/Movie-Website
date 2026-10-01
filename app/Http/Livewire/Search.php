<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;


class Search extends Component
{
    public $search = '';

    public function render()
    {
        $hasilcari = [];

        if (strlen($this->search) >= 2) {
            
            // 1. Check local database first
            $localResults = \App\Models\Media::where('title', 'like', '%' . $this->search . '%')
                                ->take(10)
                                ->get();

            if ($localResults->count() > 0) {
                // We have local results! Map them to the array format blade expects
                $hasilcari = $localResults->map(function ($media) {
                    $data = is_string($media->data) ? json_decode($media->data, true) : ($media->data ?? []);
                    $data['id'] = $media->tmdb_id; // Blade uses 'id' instead of 'tmdb_id'
                    $data['media_type'] = $media->media_type;
                    return $data;
                })->toArray();
            } else {
                // 2. Cache Miss: Fall back to TMDB API
                $hasilcari = Cache::remember('tmdb:search:' . md5($this->search), now()->addMinutes(30), function () {
                    return Http::withToken(config('services.tmdb.token'))
                        ->get('https://api.themoviedb.org/3/search/multi', ['query' => $this->search, 'include_adult' => 'false'])
                        ->json()['results'] ?? [];
                });

                // Filter to only movies and TV shows
                $hasilcari = collect($hasilcari)->filter(function ($item) {
                    return in_array($item['media_type'] ?? '', ['movie', 'tv']);
                })->values()->take(10)->toArray();

                // 3. Save these new results into our local MySQL database in ONE fast query!
                $upsertData = [];
                foreach ($hasilcari as $item) {
                    $upsertData[] = [
                        'tmdb_id' => $item['id'],
                        'media_type' => $item['media_type'],
                        'title' => $item['title'] ?? $item['name'] ?? 'Unknown',
                        'poster_path' => $item['poster_path'] ?? null,
                        'data' => json_encode($item),
                    ];
                }
                if (count($upsertData) > 0) {
                    \App\Models\Media::upsert(
                        $upsertData,
                        ['tmdb_id', 'media_type'], // Unique columns
                        ['title', 'poster_path', 'data'] // Columns to update on duplicate
                    );
                }
            }
        }

        return view('livewire.search', [
            'hasilcari' => collect($hasilcari)->take(10),
        ]);
    }
}
