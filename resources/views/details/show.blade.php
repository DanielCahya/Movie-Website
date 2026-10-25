@php
    $backdropUrl = !empty($media['backdrop_path']) 
        ? 'https://image.tmdb.org/t/p/w1280/' . ltrim($media['backdrop_path'], '/') 
        : null;
    $posterUrl = !empty($media['poster_path']) 
        ? 'https://image.tmdb.org/t/p/w500/' . ltrim($media['poster_path'], '/') 
        : asset('default-og.jpg');

    $pageTitle = ($media['title'] ?? $media['name'] ?? 'Details') . ' - Galiwe';
    $pageDesc = strlen($desc = strip_tags($media['overview'] ?? 'Discover details on Galiwe.')) > 150 ? substr($desc, 0, 150) . '...' : $desc;
    $pageOgType = $type === 'movie' ? 'video.movie' : 'video.tv_show';
    $pageOgImage = $backdropUrl ?: $posterUrl;

    $allVideos = collect($media['videos']['results'] ?? [])->filter(function($v) {
        return ($v['site'] ?? '') === 'YouTube' && !empty($v['key']);
    });
    $primaryTrailer = $allVideos->firstWhere('type', 'Trailer') ?? $allVideos->first();
    $trailerKey = $primaryTrailer['key'] ?? null;
@endphp

<x-indexLay :title="$pageTitle" :metaDescription="$pageDesc" :ogType="$pageOgType" :ogImage="$pageOgImage">

    <x-slot:schema>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "{{ $type === 'movie' ? 'Movie' : 'TVSeries' }}",
        "name": "{{ addslashes($media['title'] ?? $media['name'] ?? 'Details') }}",
        "description": "{{ addslashes(strip_tags($media['overview'] ?? 'Discover details on Galiwe.')) }}",
        "image": "{{ $posterUrl }}",
        "datePublished": "{{ $media['release_date'] ?? $media['first_air_date'] ?? '' }}",
        @if(isset($media['vote_average']) && ($media['vote_count'] ?? 0) > 0)
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "{{ number_format($media['vote_average'], 1) }}",
            "bestRating": "10",
            "ratingCount": "{{ $media['vote_count'] }}"
        },
        @endif
        @if(!empty($trailerKey))
        "trailer": {
            "@type": "VideoObject",
            "name": "{{ addslashes(($media['title'] ?? $media['name'] ?? 'Media') . ' Official Trailer') }}",
            "embedUrl": "https://www.youtube.com/embed/{{ $trailerKey }}",
            "thumbnailUrl": "https://img.youtube.com/vi/{{ $trailerKey }}/hqdefault.jpg",
            "description": "{{ addslashes(strip_tags($media['overview'] ?? 'Watch the official trailer on Galiwe.')) }}",
            "uploadDate": "{{ $media['release_date'] ?? $media['first_air_date'] ?? date('Y-m-d') }}"
        }
        @endif
    }
    </script>
    </x-slot:schema>

    <div x-data="{ isPlayingTrailer: false, activeTrailerKey: '{{ $trailerKey }}' }"
         @keydown.escape.window="isPlayingTrailer = false"
         class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-8 relative z-20">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- LEFT MAIN COLUMN -->
            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                
                <!-- 1. CINEMATIC BACKDROP & IN-PLACE TRAILER PLAYER HERO -->
                <div class="w-full bg-black rounded-2xl overflow-hidden shadow-2xl relative aspect-video border border-white/10 group">
                    
                    <!-- INLINE ACTIVE VIDEO PLAYER (Mounted on-demand when playing) -->
                    @if(!empty($trailerKey))
                    <template x-if="isPlayingTrailer && activeTrailerKey">
                        <div class="relative w-full h-full bg-black">
                            <iframe class="w-full h-full"
                                    :src="'https://www.youtube.com/embed/' + activeTrailerKey + '?autoplay=1&rel=0'"
                                    title="{{ $media['title'] ?? $media['name'] ?? 'Media' }} Trailer"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                            </iframe>
                            <!-- Floating Player Header Controls -->
                            <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none z-30">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-black/75 backdrop-blur-md border border-white/10 shadow-lg pointer-events-auto">
                                    <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
                                    <span class="text-xs font-bold text-white uppercase tracking-wider truncate max-w-[200px] sm:max-w-md">
                                        Playing Trailer: {{ $media['title'] ?? $media['name'] ?? 'Media' }}
                                    </span>
                                </div>
                                <button @click="isPlayingTrailer = false"
                                        type="button"
                                        class="pointer-events-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-black/75 hover:bg-black/95 text-gray-200 hover:text-white backdrop-blur-md border border-white/10 transition-colors shadow-lg active:scale-95 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-brand"
                                        aria-label="Close trailer and show poster">
                                    <i class='bx bx-x text-lg'></i>
                                    <span class="hidden sm:inline">Close Player</span>
                                </button>
                            </div>
                        </div>
                    </template>
                    @endif

                    <!-- CINEMATIC BACKDROP POSTER & OVERLAY (Visible when not playing) -->
                    <div x-show="!isPlayingTrailer" class="relative w-full h-full">
                        @if($backdropUrl)
                            <img src="{{ $backdropUrl }}" 
                                 alt="{{ $media['title'] ?? $media['name'] ?? 'Backdrop' }}" 
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-brand-bg via-brand-bg/60 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-brand-card to-brand-bg"></div>
                        @endif

                        <!-- Hero Overlay Content -->
                        <div class="absolute inset-0 flex flex-col justify-end p-6 sm:p-8 z-10 pointer-events-none">
                            <div class="max-w-2xl">
                                <span class="inline-block px-2.5 py-1 mb-2 text-xs font-black uppercase tracking-wider bg-brand text-gray-950 rounded">
                                    {{ strtoupper($type) }}
                                </span>
                                <h2 class="text-2xl sm:text-4xl font-black text-white leading-tight drop-shadow-md">
                                    {{ $media['title'] ?? $media['name'] ?? 'Unknown Title' }}
                                </h2>
                                @if(isset($media['tagline']) && $media['tagline'])
                                    <p class="text-sm sm:text-base text-gray-300 italic mt-1 drop-shadow">
                                        "{{ $media['tagline'] }}"
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Center Play Button (if trailer available) -->
                        @if(!empty($trailerKey))
                        <div class="absolute inset-0 flex items-center justify-center z-20 pointer-events-none">
                            <button @click="isPlayingTrailer = true" 
                                    type="button"
                                    class="pointer-events-auto flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-brand/90 hover:bg-cyan-300 text-gray-950 shadow-2xl shadow-brand/50 hover:scale-110 active:scale-95 transition-all duration-300 group/btn focus:outline-none focus:ring-4 focus:ring-brand/40"
                                    aria-label="Play Trailer">
                                <i class='bx bx-play text-4xl sm:text-5xl ml-1 group-hover/btn:scale-110 transition-transform'></i>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 2. ACTION BUTTONS & MULTI-CLIP SWITCHER -->
                <div class="space-y-3">
                    <div class="flex flex-wrap gap-3 items-center bg-brand-card/40 backdrop-blur-md p-3 rounded-xl border border-white/5">
                        @if(!empty($trailerKey))
                        <button @click="isPlayingTrailer = !isPlayingTrailer" 
                                type="button"
                                :class="isPlayingTrailer ? 'bg-white/10 hover:bg-white/20 text-white' : 'bg-brand hover:bg-cyan-400 text-gray-950 shadow-brand/20'"
                                class="flex items-center justify-center font-black py-2 px-5 text-sm rounded-lg transition-all shadow-lg active:scale-95">
                            <i class='bx' :class="isPlayingTrailer ? 'bx-stop text-xl mr-1.5' : 'bx-play text-xl mr-1.5'"></i>
                            <span x-text="isPlayingTrailer ? 'Stop Trailer' : 'Watch Trailer'"></span>
                        </button>
                        @endif

                        @auth
                        <form action="{{ route('watchlist.toggle') }}" method="POST" class="inline m-0">
                            @csrf
                            <input type="hidden" name="media_id" value="{{ $media['id'] }}">
                            <input type="hidden" name="media_type" value="{{ $type }}">
                            <button type="submit" class="flex items-center justify-center {{ $inWatchlist ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-white/10 hover:bg-white/20 text-white' }} font-bold py-2 px-4 text-sm rounded-lg transition-colors border border-white/10 shadow-sm">
                                <i class='bx {{ $inWatchlist ? "bx-minus" : "bx-plus" }} text-lg mr-1.5'></i> {{ $inWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist' }}
                            </button>
                        </form>
                        @else
                        <a wire:navigate.hover href="{{ route('login') }}" class="flex items-center justify-center bg-white/10 hover:bg-white/20 text-white font-bold py-2 px-4 text-sm rounded-lg transition-colors border border-white/10 shadow-sm">
                            <i class='bx bx-plus text-lg mr-1.5'></i> Watchlist
                        </a>
                        @endauth

                        <button onclick="shareMedia('{{ addslashes($media['title'] ?? $media['name'] ?? 'Media') }}', window.location.href)" 
                                type="button"
                                class="flex items-center justify-center bg-white/5 hover:bg-white/10 text-white font-semibold py-2 px-4 text-sm rounded-lg transition-colors border border-white/10 active:scale-95"
                                aria-label="Share this title">
                            <i class='bx bx-share-alt text-lg mr-1.5'></i> Share
                        </button>
                    </div>

                    <!-- Multi-Clip Selector Tabs (if more than 1 clip available) -->
                    @if($allVideos->count() > 1)
                    <div class="px-4 py-2.5 bg-brand-card/25 backdrop-blur-sm rounded-xl border border-white/5 flex items-center gap-2 overflow-x-auto hide-scrollbar">
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider flex-none mr-1">Clips:</span>
                        @foreach($allVideos->take(6) as $vid)
                        <button @click="activeTrailerKey = '{{ $vid['key'] }}'; isPlayingTrailer = true"
                                type="button"
                                :class="{'bg-brand text-gray-950 font-bold': activeTrailerKey === '{{ $vid['key'] }}' && isPlayingTrailer, 'bg-white/5 text-gray-300 hover:bg-white/10': activeTrailerKey !== '{{ $vid['key'] }}' || !isPlayingTrailer}"
                                class="px-3 py-1 rounded-lg text-xs transition-colors flex-none truncate max-w-[220px]"
                                title="{{ $vid['name'] }}">
                            <i class='bx bx-play-circle mr-1'></i> {{ $vid['type'] ?? 'Video' }}: {{ $vid['name'] }}
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- 3. MEDIA DETAILS CARD -->
                <div class="bg-brand-card rounded-2xl p-6 sm:p-8 shadow-xl border border-white/5 flex flex-col sm:flex-row gap-6">
                    <!-- Poster -->
                    <div class="w-full sm:w-48 lg:w-56 flex-none">
                        <div class="relative rounded-xl overflow-hidden shadow-2xl aspect-[2/3] ring-1 ring-white/10">
                            <img style="view-transition-name: poster-{{ $type }}-{{ $media['id'] }}" 
                                 src="{{ $posterUrl }}" 
                                 alt="{{ $media['title'] ?? $media['name'] ?? 'Poster' }}" 
                                 class="w-full h-full object-cover">
                            <div class="absolute top-2 left-2 bg-brand text-gray-950 text-xs font-black tracking-wider px-2 py-1 rounded shadow">
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
                            @if(isset($media['vote_count']))
                                <span class="text-gray-500 text-xs ml-2">({{ number_format($media['vote_count']) }} votes)</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-4 text-sm mb-6 bg-brand-bg/50 p-4 rounded-xl border border-white/5">
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
                                <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-lg text-xs text-gray-300 font-medium hover:bg-white/10 hover:border-brand/50 transition-colors cursor-pointer">
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
                <div x-data="{ activeSeason: {{ collect($media['seasons'])->firstWhere('season_number', '>', 0)['season_number'] ?? collect($media['seasons'])->first()['season_number'] ?? 1 }} }" class="bg-brand-card rounded-2xl border border-white/5 overflow-hidden shadow-xl">
                    <div class="bg-brand-dark/50 p-4 border-b border-white/5 flex items-center justify-between">
                        <h3 class="text-white font-bold text-sm uppercase tracking-wider">Episodes List</h3>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex items-center space-x-2 overflow-x-auto hide-scrollbar pb-3 mb-4 border-b border-white/10">
                            @foreach($media['seasons'] as $season)
                                @if($season['season_number'] > 0)
                                <button @click="activeSeason = {{ $season['season_number'] }}" 
                                        :class="{'bg-brand text-gray-900 font-bold': activeSeason === {{ $season['season_number'] }}, 'bg-white/5 text-gray-300 hover:bg-white/10': activeSeason !== {{ $season['season_number'] }}}"
                                        class="px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors text-xs font-bold flex-none">
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
                                    <div class="bg-brand-bg hover:bg-brand/20 border border-white/5 hover:border-brand/50 rounded-lg flex items-center justify-center py-2 cursor-pointer transition-all group shadow-sm">
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

                <div class="bg-brand-card rounded-2xl border border-white/5 overflow-hidden sticky top-24 shadow-xl">
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
                                    <div class="w-14 h-20 flex-none rounded-lg overflow-hidden shadow ring-1 ring-white/10 group-hover:ring-brand/50 transition-all">
                                        <img src="{{ isset($similar['poster_path']) && $similar['poster_path'] ? 'https://image.tmdb.org/t/p/w200/'.$similar['poster_path'] : 'https://via.placeholder.com/100x150' }}" alt="{{ $similar['title'] ?? $similar['name'] ?? 'Poster' }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
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

    <!-- Mobile-First Web Share & Clipboard Script -->
    <script>
        function shareMedia(title, url) {
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: 'Check out ' + title + ' on Galiwe!',
                    url: url
                }).catch(function(err) {
                    if (err.name !== 'AbortError') {
                        copyToClipboard(url);
                    }
                });
            } else {
                copyToClipboard(url);
            }
        }

        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('Link copied to clipboard!');
                }).catch(function() {
                    fallbackCopy(text);
                });
            } else {
                fallbackCopy(text);
            }
        }

        function fallbackCopy(text) {
            var textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.top = '-9999px';
            textArea.style.left = '-9999px';
            textArea.setAttribute('readonly', '');
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    alert('Link copied to clipboard!');
                } else {
                    prompt('Copy this link:', text);
                }
            } catch (err) {
                prompt('Copy this link:', text);
            }
            document.body.removeChild(textArea);
        }
    </script>

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
