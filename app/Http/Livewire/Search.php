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
            $hasilcari = Cache::remember('tmdb:search:'.md5($this->search), now()->addMinutes(30), function () {
                return Http::withToken(config('services.tmdb.token'))
                    ->get('https://api.themoviedb.org/3/search/movie', ['query' => $this->search])
                    ->json()['results'] ?? [];
            });
        }

        return view('livewire.search',[
            'hasilcari' => collect($hasilcari)->take(10),
        ]);
    }
}
