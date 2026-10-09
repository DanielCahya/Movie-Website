<x-indexLay>
<div class="bg-brand-bg min-h-[calc(100vh-4rem)] flex">
    <!-- Sidebar -->
    @include('profile.partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 p-6 lg:p-12 overflow-y-auto">
        <div class="max-w-6xl mx-auto">
            <div class="mb-10">
                <h1 class="text-2xl font-bold text-white mb-2">My Watchlist</h1>
                <p class="text-gray-400 text-sm">Titles you've saved to watch later.</p>
            </div>
            
            @if(count($mediaItems) > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @foreach($mediaItems as $item)
                        @php
                            $type = $item['media_type_custom'];
                            $title = $item['title'] ?? $item['name'] ?? 'Unknown';
                            $poster = isset($item['poster_path']) ? 'https://image.tmdb.org/t/p/w500/'.$item['poster_path'] : 'https://via.placeholder.com/500x750';
                            $year = isset($item['release_date']) || isset($item['first_air_date']) ? \Carbon\Carbon::parse($item['release_date'] ?? $item['first_air_date'])->format('Y') : '';
                            $url = route('watch', ['type' => $type, 'id' => $item['id']]);
                        @endphp
                        
                        <a href="{{ $url }}" class="group block relative overflow-hidden rounded-xl bg-brand-card ring-1 ring-white/10 hover:ring-brand/50 transition-all duration-300">
                            <!-- Poster Container -->
                            <div x-data="{ loaded: false }" class="relative aspect-[2/3] overflow-hidden bg-brand-card">
                                <div x-show="!loaded" class="absolute inset-0 bg-gray-800 animate-pulse z-10"></div>
                                <img x-init="$el.complete && (loaded = true)" @load="loaded = true" :class="loaded ? 'opacity-100' : 'opacity-0'" src="{{ $poster }}" alt="{{ $title }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-all duration-500">
                                <!-- Top Badges -->
                                <div class="absolute top-2 left-2 flex gap-1">
                                    <span class="bg-brand text-gray-900 text-[10px] font-black uppercase px-2 py-0.5 rounded shadow">{{ $type }}</span>
                                </div>
                                <div class="absolute top-2 right-2">
                                    <span class="bg-black/60 backdrop-blur-sm text-yellow-400 text-[10px] font-bold px-1.5 py-0.5 rounded shadow flex items-center">
                                        <i class='bx bxs-star mr-1'></i>{{ number_format($item['vote_average'] ?? 0, 1) }}
                                    </span>
                                </div>
                                
                                <!-- Play Overlay -->
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-brand/90 flex items-center justify-center text-gray-900 shadow-[0_0_15px_rgba(6,182,212,0.5)] transform scale-75 group-hover:scale-100 transition-transform duration-300 delay-100">
                                        <i class='bx bx-play text-2xl ml-1'></i>
                                    </div>
                                </div>
                            </div>
                            <!-- Title Area -->
                            <div class="p-3">
                                <h3 class="text-white text-sm font-semibold truncate group-hover:text-brand transition-colors">{{ $title }}</h3>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-gray-500 text-xs">{{ $year }}</span>
                                    
                                    <!-- Remove button -->
                                    <form action="{{ route('watchlist.toggle') }}" method="POST" class="inline" onclick="event.preventDefault(); event.stopPropagation(); this.submit();">
                                        @csrf
                                        <input type="hidden" name="media_id" value="{{ $item['id'] }}">
                                        <input type="hidden" name="media_type" value="{{ $type }}">
                                        <button type="submit" class="text-gray-500 hover:text-red-400 transition-colors" title="Remove from Watchlist">
                                            <i class='bx bx-bookmark-minus text-lg'></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-brand-card/50 border border-white/5 rounded-xl p-12 text-center">
                    <i class='bx bx-bookmark text-6xl text-gray-700 mb-4'></i>
                    <h3 class="text-xl font-bold text-white mb-2">Your Watchlist is empty</h3>
                    <p class="text-gray-500 mb-6">Discover movies and TV shows and save them here for later.</p>
                    <a wire:navigate.hover href="{{ url('home') }}" class="inline-block bg-brand hover:bg-cyan-400 text-gray-900 font-bold py-2.5 px-6 rounded-lg transition-colors shadow-lg shadow-brand/20">
                        Explore Now
                    </a>
                </div>
            @endif
        </div>
    </main>
</div>
</x-indexLay>
