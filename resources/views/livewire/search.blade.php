<div class="relative z-50">
    <div class="relative flex items-center">
        <i class='bx bx-search absolute left-3 text-gray-400 text-lg'></i>
        <input wire:model.debounce.300ms="search" type="text" class="bg-gray-800/50 border border-gray-700 rounded-full w-48 focus:w-64 md:w-64 transition-all duration-300 px-4 pl-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent text-white placeholder-gray-400" placeholder="Search movies...">
    </div>
    
    @if (strlen($search) >= 2)
        <div class="absolute mt-2 bg-brand-card border border-gray-700/50 shadow-2xl shadow-brand-bg rounded-xl w-80 max-h-96 overflow-y-auto right-0 md:left-0 md:right-auto z-50">
            @if ($hasilcari->count() > 0)
                <ul class="py-2">
                    @foreach ($hasilcari as $hasilcari)
                        <li>
                            <a href="{{url('movieshow',$hasilcari['id'])}}" class="flex items-center gap-4 px-4 py-3 hover:bg-white/5 transition-colors border-b border-gray-700/30 last:border-0">
                                @if ($hasilcari['poster_path'])
                                    <img src="https://image.tmdb.org/t/p/w92/{{$hasilcari['poster_path']}}" alt="{{ $hasilcari['title'] }}" class="w-12 rounded shadow-sm">
                                @else
                                    <div class="w-12 h-16 bg-gray-800 rounded flex items-center justify-center shadow-sm">
                                        <i class='bx bx-movie text-gray-500'></i>
                                    </div>
                                @endif
                                <span class="text-white text-sm md:text-base font-medium line-clamp-2">{{ $hasilcari['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-4 py-6 text-center text-gray-400 text-sm">No results found for "<span class="text-white">{{ $search }}</span>"</div>
            @endif
        </div>
    @endif
</div>
