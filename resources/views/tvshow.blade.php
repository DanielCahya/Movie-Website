<x-indexLay>
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-12 relative z-20">
        <h2 class="text-3xl font-bold text-white mb-8 border-l-4 border-brand pl-4">All TV Shows</h2>
        <div class="spotlight-group grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 relative z-20">
            @foreach ($top as $show)
            <a href="{{ route('watch', ['type' => 'tv', 'id' => $show['id']]) }}" class="spotlight-card block relative cursor-pointer">
                <div x-data="{ loaded: false }" class="relative rounded-xl overflow-hidden mb-3 aspect-[2/3] bg-brand-card shadow-lg ring-1 ring-white/5 transition-all duration-300">
                    <div x-show="!loaded" class="absolute inset-0 bg-gray-800 animate-pulse"></div>
                    <img style="view-transition-name: poster-tv-{{ $show['id'] }}" x-init="$el.complete && (loaded = true)" @load="loaded = true" :class="loaded ? 'opacity-100' : 'opacity-0'" src="{{'https://image.tmdb.org/t/p/w500/'.$show['poster_path']}}" alt="{{$show['name']}}" class="w-full h-full object-cover transition-all duration-500" loading="lazy">
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <i class="bx bx-play-circle text-5xl text-brand drop-shadow-md"></i>
                    </div>
                </div>
                <h3 class="text-white font-semibold text-sm md:text-base truncate transition-colors" title="{{$show['name']}}">{{$show['name']}}</h3>
            </a>
            @endforeach
        </div>
    </div>
</x-indexLay>