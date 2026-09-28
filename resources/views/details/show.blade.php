<x-indexLay>
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-8 relative z-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- LEFT MAIN COLUMN -->
            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                
                <!-- 1. EMBEDDED VIDEO PLAYER -->
                <div class="w-full bg-black rounded-xl overflow-hidden shadow-2xl relative aspect-video border border-white/5">
                    @if (isset($media['videos']['results']) && count($media['videos']['results']) > 0)
                        <iframe class="absolute top-0 left-0 w-full h-full" 
                                src="https://www.youtube.com/embed/{{ $media['videos']['results'][0]['key'] }}" 
                                frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    @else
                        <div class="flex flex-col items-center justify-center w-full h-full bg-brand-card">
                            <i class='bx bx-video-off text-6xl text-gray-700 mb-4'></i>
                            <p class="text-gray-500 text-lg">No trailer available for this title.</p>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons (Watchlist etc) -->
                <div class="flex flex-wrap gap-3 items-center bg-brand-card/30 p-2.5 rounded-lg border border-white/5">
                    @auth
                    <form action="{{ route('watchlist.toggle') }}" method="POST" class="inline m-0">
                        @csrf
                        <input type="hidden" name="media_id" value="{{ $media['id'] }}">
                        <input type="hidden" name="media_type" value="{{ $type }}">
                        <button type="submit" class="flex items-center justify-center {{ $inWatchlist ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-brand hover:bg-cyan-400 text-gray-900' }} font-bold py-1.5 px-4 text-sm rounded transition-colors shadow shadow-black/20">
                            <i class='bx {{ $inWatchlist ? "bx-minus" : "bx-plus" }} text-lg mr-1.5'></i> {{ $inWatchlist ? 'Remove from Watchlist' : 'Watchlist' }}
                        </button>
                    </form>
                    @else
                    <a href="{{ route('login') }}" class="flex items-center justify-center bg-brand hover:bg-cyan-400 text-gray-900 font-bold py-1.5 px-4 text-sm rounded transition-colors shadow shadow-brand/20">
                        <i class='bx bx-plus text-lg mr-1.5'></i> Watchlist
                    </a>
                    @endauth
                    <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');" class="flex items-center justify-center bg-white/5 hover:bg-white/10 text-white font-semibold py-1.5 px-4 text-sm rounded transition-colors border border-white/10">
                        <i class='bx bx-share-alt text-lg mr-1.5'></i> Share
                    </button>
                </div>


                <!-- 3. MEDIA DETAILS CARD-->
                <div class="bg-brand-card rounded-xl p-4 sm:p-6 shadow-lg border border-white/5 flex flex-col sm:flex-row gap-6">
                    <!-- Poster -->
                    <div class="w-full sm:w-48 lg:w-56 flex-none">
                        <div class="relative rounded-lg overflow-hidden shadow-xl aspect-[2/3] ring-1 ring-white/10">
                            <img src="{{ isset($media['poster_path']) && $media['poster_path'] ? 'https://image.tmdb.org/t/p/w500/'.$media['poster_path'] : 'https://via.placeholder.com/500x750' }}" alt="Poster" class="w-full h-full object-cover">
                            <div class="absolute top-2 left-2 bg-brand text-gray-900 text-xs font-black tracking-wider px-2 py-1 rounded shadow">
                                {{ strtoupper($type) }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Info Grid -->
                    <div class="flex-grow flex flex-col">
                        <h1 class="text-2xl sm:text-3xl font-black text-white mb-1">
                            {{ $media['title'] ?? $media['name'] ?? 'Unknown Title' }}
                        </h1>
                        @if(isset($media['original_title']) || isset($media['original_name']))
                        <p class="text-gray-400 text-sm mb-4 font-medium tracking-wide">
                            {{ $media['original_title'] ?? $media['original_name'] }}
                        </p>
                        @endif

                        <div class="flex items-center text-yellow-500 font-bold mb-5">
                            <div class="flex space-x-1 mr-2 text-sm">
                                <i class='bx bxs-star'></i>
                                <i class='bx bxs-star'></i>
                                <i class='bx bxs-star'></i>
                                <i class='bx bxs-star'></i>
                                <i class='bx bxs-star-half'></i>
                            </div>
                            <span class="text-white text-base">{{ number_format($media['vote_average'] ?? 0, 1) }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-4 text-sm mb-6 bg-brand-bg/50 p-4 rounded-lg border border-white/5">
                            <div class="flex">
                                <span class="text-gray-500 font-semibold w-24 flex-none">Status:</span>
                                <span class="text-gray-200">{{ $media['status'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-500 font-semibold w-24 flex-none">Type:</span>
                                <span class="text-gray-200">{{ strtoupper($type) }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-500 font-semibold w-24 flex-none">Studio:</span>
                                <span class="text-gray-200 truncate">{{ isset($media['production_companies']) && count($media['production_companies']) > 0 ? $media['production_companies'][0]['name'] : 'Unknown' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-500 font-semibold w-24 flex-none">Released:</span>
                                <span class="text-gray-200">{{ isset($media['release_date']) || isset($media['first_air_date']) ? \Carbon\Carbon::parse($media['release_date'] ?? $media['first_air_date'])->format('M d, Y') : 'Unknown' }}</span>
                            </div>
                            <div class="flex">
                                <span class="text-gray-500 font-semibold w-24 flex-none">Duration:</span>
                                <span class="text-gray-200">
                                    {{ isset($media['runtime']) && $media['runtime'] ? $media['runtime'].' min' : (isset($media['episode_run_time']) && count($media['episode_run_time']) > 0 ? $media['episode_run_time'][0].' min' : 'N/A') }}
                                </span>
                            </div>
                            @if($type === 'tv' && isset($media['number_of_episodes']))
                            <div class="flex">
                                <span class="text-gray-500 font-semibold w-24 flex-none">Episodes:</span>
                                <span class="text-gray-200">{{ $media['number_of_episodes'] }}</span>
                            </div>
                            @endif
                        </div>

                        <!-- Genres -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach ($media['genres'] ?? [] as $genre)
                                <span class="px-3 py-1 bg-white/5 border border-white/10 rounded text-xs text-gray-300 font-medium hover:bg-white/10 hover:border-brand/50 transition-colors cursor-pointer">
                                    {{$genre['name']}}
                                </span>
                            @endforeach
                        </div>

                        <!-- Description -->
                        <div class="mt-2 text-gray-400 text-sm leading-relaxed border-t border-white/5 pt-4">
                            {{ $media['overview'] ?? 'No description available.' }}
                        </div>
                    </div>
                </div>

                <!-- 4. COMMENTS SECTION -->
                @include('details.partials.comments')

            </div>

            <!-- RIGHT SIDEBAR (Similar/Trending) -->
            <div class="lg:col-span-4 xl:col-span-3 relative space-y-6">

                <!-- TV SHOW EPISODES (Only if TV) -->
                @if($type === 'tv' && isset($media['seasons']) && count($media['seasons']) > 0)
                <div x-data="{ activeSeason: {{ collect($media['seasons'])->firstWhere('season_number', '>', 0)['season_number'] ?? collect($media['seasons'])->first()['season_number'] ?? 1 }} }" class="bg-brand-card rounded-xl border border-white/5 overflow-hidden shadow-lg">
                    <div class="bg-brand-dark/50 p-4 border-b border-white/5 flex items-center justify-between">
                        <h3 class="text-white font-bold text-sm uppercase tracking-wider">Episodes List</h3>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex items-center space-x-2 overflow-x-auto hide-scrollbar pb-3 mb-4 border-b border-white/10">
                            @foreach($media['seasons'] as $season)
                                @if($season['season_number'] > 0)
                                <button @click="activeSeason = {{ $season['season_number'] }}" 
                                        :class="{'bg-brand text-gray-900 font-bold': activeSeason === {{ $season['season_number'] }}, 'bg-white/5 text-gray-300 hover:bg-white/10': activeSeason !== {{ $season['season_number'] }}}"
                                        class="px-3 py-1.5 rounded whitespace-nowrap transition-colors text-xs font-bold flex-none">
                                    {{ $season['name'] }}
                                </button>
                                @endif
                            @endforeach
                        </div>
                        
                        <!-- Display active season episodes -->
                        <div class="max-h-[350px] overflow-y-auto hide-scrollbar pr-1">
                        @foreach($media['seasons'] as $season)
                            @if($season['season_number'] > 0)
                            <div x-show="activeSeason === {{ $season['season_number'] }}" x-transition.opacity.duration.300ms class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-4 gap-2" style="display: none;">
                                @if($season['episode_count'] > 0)
                                    @for($i = 1; $i <= min($season['episode_count'], 100); $i++)
                                    <div class="bg-brand-bg hover:bg-brand/20 border border-white/5 hover:border-brand/50 rounded flex items-center justify-center py-2 cursor-pointer transition-all group shadow-sm">
                                        <span class="text-sm font-bold text-gray-300 group-hover:text-brand transition-colors">{{ $i }}</span>
                                    </div>
                                    @endfor
                                @else
                                    <div class="col-span-full py-6 text-center text-gray-500 text-sm">
                                        No episodes yet.
                                    </div>
                                @endif
                            </div>
                            @endif
                        @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="bg-brand-card rounded-xl border border-white/5 overflow-hidden sticky top-24 shadow-lg">
                    <div class="bg-brand-dark/50 p-4 border-b border-white/5 flex items-center justify-between">
                        <h3 class="text-white font-bold text-sm uppercase tracking-wider flex items-center">
                            <i class='bx bx-trending-up text-brand text-lg mr-2'></i> 
                            Similar {{$type === 'movie' ? 'Movies' : 'Shows'}}
                        </h3>
                    </div>
                    
                    <div class="p-4 flex flex-col gap-4">
                        @if(isset($media['similar']['results']) && count($media['similar']['results']) > 0)
                            @foreach (array_slice($media['similar']['results'], 0, 8) as $index => $similar)
                                @php
                                    $similarUrl = route('watch', ['type' => $type, 'id' => $similar['id']]);
                                @endphp
                                <a href="{{ $similarUrl }}" class="flex items-center gap-3 group">
                                    <div class="w-6 flex-none text-center">
                                        <span class="text-{{ $index < 3 ? 'brand' : 'gray-600' }} font-bold text-base">{{ $index + 1 }}</span>
                                    </div>
                                    <div class="w-14 h-20 flex-none rounded overflow-hidden shadow ring-1 ring-white/10 group-hover:ring-brand/50 transition-all">
                                        <img src="{{ isset($similar['poster_path']) && $similar['poster_path'] ? 'https://image.tmdb.org/t/p/w200/'.$similar['poster_path'] : 'https://via.placeholder.com/100x150' }}" alt="Poster" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    </div>
                                    <div class="flex-grow overflow-hidden">
                                        <h4 class="text-white text-sm font-semibold truncate group-hover:text-brand transition-colors" title="{{ $similar['title'] ?? $similar['name'] }}">{{ $similar['title'] ?? $similar['name'] }}</h4>
                                        <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                            <span class="text-[10px] font-bold text-brand bg-brand/10 border border-brand/20 rounded px-1.5 py-0.5">{{ strtoupper($type) }}</span>
                                            <span class="text-xs text-yellow-500 flex items-center"><i class='bx bxs-star mr-1'></i>{{ number_format($similar['vote_average'], 1) }}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 truncate">
                                            {{ isset($similar['release_date']) || isset($similar['first_air_date']) ? \Carbon\Carbon::parse($similar['release_date'] ?? $similar['first_air_date'])->format('Y') : '' }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        @else
                            <p class="text-gray-500 text-sm text-center py-4">No similar titles found.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
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
