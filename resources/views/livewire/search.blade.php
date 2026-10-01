<div class="relative z-50">
    <div class="relative flex items-center">
        <i class='bx bx-search absolute left-3 text-gray-400 text-lg'></i>
        <input wire:model.debounce.300ms="search" type="text" class="bg-gray-800/50 border border-gray-700 rounded-full w-48 focus:w-64 md:w-64 transition-all duration-300 px-4 pl-10 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent text-white placeholder-gray-400" placeholder="Search...">
        <!-- Loading Spinner -->
        <div wire:loading wire:target="search" class="absolute right-3">
            <i class='bx bx-loader-alt animate-spin text-brand text-lg'></i>
        </div>
    </div>
    
    @if (strlen($search) >= 2)
        <div class="absolute mt-2 bg-brand-card border border-gray-700/50 shadow-2xl shadow-brand-bg rounded-xl w-80 max-h-96 overflow-y-auto right-0 md:left-0 md:right-auto z-50">
            @if ($hasilcari->count() > 0)
                <ul class="py-2">
                    @foreach ($hasilcari as $item)
                        <li>
                            <a href="{{ route('watch', ['type' => $item['media_type'] ?? 'movie', 'id' => $item['id']]) }}" class="flex items-center gap-4 px-4 py-3 hover:bg-white/5 transition-colors border-b border-gray-700/30 last:border-0 group">
                                @if (isset($item['poster_path']) && $item['poster_path'])
                                    <img style="view-transition-name: poster-{{ $item['media_type'] ?? 'movie' }}-{{ $item['id'] }}" src="https://image.tmdb.org/t/p/w92/{{$item['poster_path']}}" alt="{{ $item['title'] ?? $item['name'] }}" class="w-10 rounded shadow-sm group-hover:scale-105 transition-transform">
                                @else
                                    <div class="w-10 h-14 bg-gray-800 rounded flex items-center justify-center shadow-sm">
                                        <i class='bx bx-movie text-gray-500'></i>
                                    </div>
                                @endif
                                <div class="flex flex-col">
                                    <span class="text-white text-sm font-medium line-clamp-1 group-hover:text-brand transition-colors">{{ $item['title'] ?? $item['name'] }}</span>
                                    <span class="text-xs text-gray-500 uppercase tracking-widest mt-1">{{ $item['media_type'] === 'tv' ? 'TV Show' : 'Movie' }}</span>
                                </div>
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
