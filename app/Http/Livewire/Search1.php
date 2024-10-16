<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;


class Search1 extends Component
{
    public $search = '';
    public function render()
    {
        $hasilcaritv = [];

        if (strlen($this->search) >= 2) {
            $hasilcaritv = Http::withToken(config('services.tmdb.token'))
            ->get('https://api.themoviedb.org/3/search/tv?query='.$this->search)
            ->json()['results'];
        }

        return view('livewire.search1',[
            'hasilcaritv' => collect($hasilcaritv)->take(10),
        ]);
    }
}
