<x-indexLay>
    <!-- HERO SECTION -->
    @if(count($topRated) > 0)
    <div x-data="{ activeIndex: 0, interval: null, slides: {{ min(5, count($topRated)) }} }"
         x-init="interval = setInterval(() => { activeIndex = (activeIndex + 1) % slides }, 5000)"
         class="relative w-full h-[85vh] min-h-[600px] flex items-center justify-start overflow-hidden bg-brand-bg">
        
        @foreach(array_slice($topRated, 0, 5) as $index => $hero)
        <div x-show="activeIndex === {{ $index }}" 
             x-transition:enter="transition ease-out duration-1000"
             x-transition:enter-start="opacity-0 scale-105"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-1000"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-105"
             class="absolute inset-0 w-full h-full"
             style="display: {{ $index === 0 ? 'block' : 'none' }};"
        >
            <!-- Background Image -->
            <img src="{{'https://image.tmdb.org/t/p/original/'.$hero['backdrop_path']}}" alt="{{ $hero['title'] }}" class="absolute inset-0 w-full h-full object-cover object-top opacity-60">
            <!-- Gradients for blending -->
            <div class="absolute inset-0 bg-gradient-to-r from-brand-bg via-brand-bg/80 to-transparent z-0"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-brand-bg via-transparent to-transparent z-0"></div>
            
            <!-- Hero Content -->
            <div class="absolute inset-0 flex items-center justify-start z-10">
                <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 pt-20">
                    <div class="max-w-3xl">
                        <h1 class="text-5xl md:text-7xl font-black text-white uppercase tracking-wider mb-2 drop-shadow-lg">
                            {{ $hero['title'] }}
                        </h1>
                        
                        <div class="flex items-center space-x-4 mb-6 text-sm md:text-base font-medium">
                            <div class="flex items-center space-x-1 text-yellow-500">
                                <i class='bx bxs-star'></i>
                                <span class="text-white">{{ $hero['vote_average'] }}</span>
                            </div>
                            <span class="text-gray-500">|</span>
                            <span class="text-gray-300">{{\Carbon\Carbon::parse($hero['release_date'])->format('Y')}}</span>
                            <span class="text-gray-500">|</span>
                            <div class="text-gray-300 space-x-2">
                                @foreach (array_slice($hero['genre_ids'], 0, 3) as $genre)
                                    <span>{{$genres->get($genre) ?? ''}}@if (!$loop->last) <span class="text-gray-600 px-1">|</span> @endif</span>
                                @endforeach
                            </div>
                        </div>
                        
                        <p class="text-gray-400 text-sm md:text-base leading-relaxed mb-8 line-clamp-3 md:line-clamp-4 max-w-xl">
                            {{ $hero['overview'] }}
                        </p>
                        
                        <div class="flex items-center space-x-4">
                            <a href="{{url('show', $hero['id'])}}" class="flex items-center justify-center bg-brand-dark hover:bg-teal-600 text-white font-semibold py-3 px-8 rounded transition-colors shadow-lg shadow-brand-dark/30">
                                <i class='bx bx-info-circle text-2xl mr-2'></i>
                                Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Carousel Indicators -->
        <div class="absolute bottom-28 left-0 right-0 z-20 flex justify-center space-x-3">
            @foreach(array_slice($topRated, 0, 5) as $index => $hero)
            <button @click="activeIndex = {{ $index }}; clearInterval(interval); interval = setInterval(() => { activeIndex = (activeIndex + 1) % slides }, 5000)" 
                    :class="{'bg-brand w-8': activeIndex === {{ $index }}, 'bg-white/30 hover:bg-white/50 w-2': activeIndex !== {{ $index }}}"
                    class="h-2 rounded-full transition-all duration-300"></button>
            @endforeach
        </div>
    </div>
    @endif
    <!-- END HERO SECTION -->

    <!-- REUSABLE GRID CAROUSEL COMPONENT -->
    @php
        // Helper to render the movie card
        $renderCard = function($item, $type = 'movie', $genreMap) {
            $id = $item['id'];
            $title = $item['title'] ?? $item['name'];
            $poster = $item['poster_path'];
            $url = $type === 'movie' ? url('show', $id) : url('showv', $id);
            $year = isset($item['release_date']) ? \Carbon\Carbon::parse($item['release_date'])->format('Y') : 
                    (isset($item['first_air_date']) ? \Carbon\Carbon::parse($item['first_air_date'])->format('Y') : '');
            
            return '
                <a href="'.$url.'" class="flex-none w-36 md:w-48 xl:w-56 group cursor-pointer snap-start">
                    <div class="relative rounded-xl overflow-hidden mb-3 aspect-[2/3] bg-brand-card shadow-lg transition-transform duration-300 group-hover:scale-105 group-hover:ring-2 group-hover:ring-brand/50">
                        <img src="https://image.tmdb.org/t/p/w500/'.$poster.'" alt="'.htmlspecialchars($title).'" class="w-full h-full object-cover" loading="lazy">
                        <!-- Play Overlay -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <i class="bx bx-play-circle text-5xl text-brand drop-shadow-md"></i>
                        </div>
                    </div>
                    <h3 class="text-white font-semibold text-sm md:text-base truncate" title="'.htmlspecialchars($title).'">'.htmlspecialchars($title).'</h3>
                    <div class="flex justify-between items-center text-xs text-gray-500 mt-1">
                        <span>'.$year.'</span>
                        <div class="flex items-center text-yellow-500"><i class="bx bxs-star mr-1"></i>'.$item['vote_average'].'</div>
                    </div>
                </a>
            ';
        };
    @endphp

    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-12 space-y-16 -mt-20 relative z-20">
        
        <!-- NOW PLAYING -->
        <section>
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-white border-l-4 border-brand pl-3">Now Playing</h2>
            </div>
            <div class="flex overflow-x-auto space-x-4 md:space-x-6 pb-6 pt-2 snap-x snap-mandatory hide-scrollbar">
                @foreach ($nowPlaying as $movie)
                    {!! $renderCard($movie, 'movie', $genres) !!}
                @endforeach
            </div>
        </section>

        <!-- TRENDING MOVIES -->
        <section>
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-white border-l-4 border-brand pl-3">Trending Movies</h2>
            </div>
            <div class="flex overflow-x-auto space-x-4 md:space-x-6 pb-6 pt-2 snap-x snap-mandatory hide-scrollbar">
                @foreach ($popularMovies as $movie)
                    {!! $renderCard($movie, 'movie', $genres) !!}
                @endforeach
            </div>
        </section>

        <!-- NEW TV SHOWS -->
        <section>
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-white border-l-4 border-brand pl-3">New TV Shows</h2>
            </div>
            <div class="flex overflow-x-auto space-x-4 md:space-x-6 pb-6 pt-2 snap-x snap-mandatory hide-scrollbar">
                @foreach ($newtv as $tv)
                    {!! $renderCard($tv, 'tv', $genresTv) !!}
                @endforeach
            </div>
        </section>

        <!-- ANIME -->
        <section>
            <div class="flex justify-between items-end mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-white border-l-4 border-brand pl-3">Popular Anime</h2>
            </div>
            <div class="flex overflow-x-auto space-x-4 md:space-x-6 pb-6 pt-2 snap-x snap-mandatory hide-scrollbar">
                @foreach ($anime as $ani)
                    {!! $renderCard($ani, 'movie', $genres) !!}
                @endforeach
            </div>
        </section>
        
    </div>

    <style>
        /* Hide scrollbar for Chrome, Safari and Opera */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .hide-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</x-indexLay>