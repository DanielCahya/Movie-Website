<x-indexLay>
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-10 relative z-20 max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight border-l-4 border-brand pl-4">
                Anime & Animation
            </h1>
            <p class="text-gray-400 text-sm mt-2 pl-4">
                Discover popular animated series and anime movies served directly from our high-performance local catalog.
            </p>
        </div>

        <!-- Filter & Search Controls Bar -->
        <div class="bg-brand-card/70 backdrop-blur-xl border border-white/10 rounded-2xl p-4 sm:p-5 mb-10 shadow-2xl">
            <form method="GET" action="{{ url('animation') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 sm:gap-4 items-center">
                <!-- Search Keyword -->
                <div class="lg:col-span-4 relative">
                    <i class="bx bx-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $searchQuery ?? '' }}" 
                        placeholder="Search anime by title..." 
                        class="w-full bg-slate-900/90 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand focus:border-transparent transition-all"
                    >
                </div>

                <!-- Format Selector -->
                <div class="lg:col-span-2 relative">
                    <select 
                        name="format" 
                        class="w-full appearance-none bg-slate-900/90 border border-slate-700/80 rounded-xl pl-4 pr-10 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand transition-all cursor-pointer"
                    >
                        <option value="">All Formats</option>
                        <option value="tv" {{ ($selectedFormat ?? '') === 'tv' ? 'selected' : '' }}>TV Series</option>
                        <option value="movie" {{ ($selectedFormat ?? '') === 'movie' ? 'selected' : '' }}>Movies</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                        <i class="bx bx-chevron-down text-lg"></i>
                    </div>
                </div>

                <!-- Genre Filter (Animation genre vanished) -->
                <div class="lg:col-span-2 relative">
                    <select 
                        name="genre" 
                        class="w-full appearance-none bg-slate-900/90 border border-slate-700/80 rounded-xl pl-4 pr-10 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand transition-all cursor-pointer"
                    >
                        <option value="">All Genres</option>
                        @foreach ($genres ?? [] as $id => $genreItem)
                            @php
                                $genreOptId = is_array($genreItem) ? ($genreItem['id'] ?? $id) : $id;
                                $genreOptName = is_array($genreItem) ? ($genreItem['name'] ?? 'Genre ' . $genreOptId) : (string) $genreItem;
                            @endphp
                            @if ((int) $genreOptId !== 16 && strcasecmp(trim($genreOptName), 'Animation') !== 0)
                                <option value="{{ $genreOptId }}" {{ (string)($selectedGenre ?? '') === (string)$genreOptId ? 'selected' : '' }}>
                                    {{ $genreOptName }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                        <i class="bx bx-chevron-down text-lg"></i>
                    </div>
                </div>

                <!-- Sort Filter -->
                <div class="lg:col-span-2 relative">
                    <select 
                        name="sort" 
                        class="w-full appearance-none bg-slate-900/90 border border-slate-700/80 rounded-xl pl-4 pr-10 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand transition-all cursor-pointer"
                    >
                        <option value="latest" {{ ($selectedSort ?? 'latest') === 'latest' ? 'selected' : '' }}>Recently Added</option>
                        <option value="rating" {{ ($selectedSort ?? '') === 'rating' ? 'selected' : '' }}>Highest Rated</option>
                        <option value="title_asc" {{ ($selectedSort ?? '') === 'title_asc' ? 'selected' : '' }}>Title (A - Z)</option>
                        <option value="title_desc" {{ ($selectedSort ?? '') === 'title_desc' ? 'selected' : '' }}>Title (Z - A)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                        <i class="bx bx-chevron-down text-lg"></i>
                    </div>
                </div>

                <!-- Actions -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button 
                        type="submit" 
                        class="flex-1 bg-brand hover:bg-cyan-400 text-brand-bg font-bold py-2.5 px-4 rounded-xl text-sm transition-all duration-200 flex items-center justify-center gap-1.5 shadow-md shadow-brand/10"
                    >
                        <i class="bx bx-filter-alt text-base"></i> Filter
                    </button>
                    @if(!empty($searchQuery) || !empty($selectedGenre) || !empty($selectedFormat) || ($selectedSort ?? 'latest') !== 'latest')
                        <a 
                            href="{{ url('animation') }}" 
                            title="Reset all filters"
                            class="bg-slate-800 hover:bg-slate-700 text-gray-300 hover:text-white p-2.5 rounded-xl text-sm transition-colors border border-slate-700 flex items-center justify-center"
                        >
                            <i class="bx bx-reset text-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Anime Grid -->
        @php
            $itemList = $items ?? $anime ?? $topAn ?? [];
        @endphp

        @if(count($itemList) > 0)
            <div class="spotlight-group grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 relative z-20">
                @foreach ($itemList as $item)
                @php
                    if (is_object($item)) {
                        $tmdbId = $item->tmdb_id ?? $item->id ?? null;
                        $title = $item->title ?? $item->name ?? 'Unknown';
                        $poster = $item->poster_path ?? null;
                        $itemData = is_array($item->data ?? null) ? $item->data : [];
                        $voteAverage = $item->vote_average ?? ($itemData['vote_average'] ?? null);
                        $mediaType = $item->media_type ?? 'tv';
                    } elseif (is_array($item)) {
                        $tmdbId = $item['tmdb_id'] ?? $item['id'] ?? null;
                        $title = $item['title'] ?? $item['name'] ?? 'Unknown';
                        $poster = $item['poster_path'] ?? null;
                        $itemData = is_array($item['data'] ?? null) ? $item['data'] : [];
                        $voteAverage = $item['vote_average'] ?? ($itemData['vote_average'] ?? null);
                        $mediaType = $item['media_type'] ?? 'tv';
                    } else {
                        $tmdbId = null;
                        $title = 'Unknown';
                        $poster = null;
                        $voteAverage = null;
                        $mediaType = 'tv';
                    }
                @endphp
                <a wire:navigate.hover href="{{ route('watch', ['type' => $mediaType, 'id' => $tmdbId]) }}" class="spotlight-card block relative cursor-pointer group">
                    <div x-data="{ loaded: false }" class="relative rounded-xl overflow-hidden mb-3 aspect-[2/3] bg-brand-card shadow-lg ring-1 ring-white/5 transition-all duration-300">
                        <div x-show="!loaded" class="absolute inset-0 bg-gray-800 animate-pulse"></div>
                        @if(!empty($poster))
                            <img 
                                style="view-transition-name: poster-anime-{{ $tmdbId }}" 
                                x-init="$el.complete && (loaded = true)" 
                                @load="loaded = true" 
                                :class="loaded ? 'opacity-100' : 'opacity-0'" 
                                src="{{ 'https://image.tmdb.org/t/p/w500/' . ltrim($poster, '/') }}" 
                                alt="{{ $title }}" 
                                class="w-full h-full object-cover transition-all duration-500" 
                                loading="lazy"
                            >
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-slate-800/90 text-gray-500 p-4 text-center">
                                <i class="bx bx-play-circle text-4xl mb-2 text-brand/40"></i>
                                <span class="text-xs line-clamp-2">{{ $title }}</span>
                            </div>
                        @endif

                        @if(!empty($voteAverage) && (float)$voteAverage > 0)
                            <div class="absolute top-2 right-2 bg-black/75 backdrop-blur-md text-amber-400 text-xs font-bold px-2 py-0.5 rounded-full flex items-center gap-1 border border-amber-400/20 shadow-lg">
                                <i class="bx bxs-star text-xs"></i> {{ number_format((float)$voteAverage, 1) }}
                            </div>
                        @endif

                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <i class="bx bx-play-circle text-5xl text-brand drop-shadow-md"></i>
                        </div>
                    </div>
                    <h3 class="text-white font-semibold text-sm md:text-base truncate transition-colors group-hover:text-brand" title="{{ $title }}">
                        {{ $title }}
                    </h3>
                </a>
                @endforeach
            </div>

            <!-- Pagination Links -->
            @if($itemList instanceof \Illuminate\Pagination\LengthAwarePaginator || (is_object($itemList) && method_exists($itemList, 'links')))
                <div class="mt-12 flex justify-center">
                    {{ $itemList->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-brand-card/40 border border-white/5 rounded-2xl p-12 text-center my-8 shadow-xl">
                <div class="w-16 h-16 bg-slate-800 text-brand rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                    <i class="bx bx-film"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No anime found</h3>
                <p class="text-gray-400 text-sm max-w-md mx-auto mb-6">
                    We couldn't find any anime matching your current search or filter criteria. Try adjusting your search term or clearing active filters.
                </p>
                <a href="{{ url('animation') }}" class="inline-flex items-center gap-2 bg-brand hover:bg-cyan-400 text-brand-bg font-semibold px-5 py-2.5 rounded-xl transition-colors text-sm">
                    <i class="bx bx-refresh text-lg"></i> Clear All Filters
                </a>
            </div>
        @endif
    </div>
</x-indexLay>
