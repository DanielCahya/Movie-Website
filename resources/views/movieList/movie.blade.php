<x-indexLay>
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-12 relative z-20">
        <h2 class="text-3xl font-bold text-white mb-8 border-l-4 border-brand pl-4">All Movies</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
            @foreach ($topRated as $movie)
            <a href="{{url('movieshow', $movie['id'])}}" class="group cursor-pointer">
                <div class="relative rounded-xl overflow-hidden mb-3 aspect-[2/3] bg-brand-card shadow-lg transition-transform duration-300 group-hover:scale-105 group-hover:ring-2 group-hover:ring-brand/50">
                    <img src="{{'https://image.tmdb.org/t/p/w500/'.$movie['poster_path']}}" alt="{{$movie['title']}}" class="w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                        <i class="bx bx-play-circle text-5xl text-brand drop-shadow-md"></i>
                    </div>
                </div>
                <h3 class="text-white font-semibold text-sm md:text-base truncate" title="{{$movie['title']}}">{{$movie['title']}}</h3>
            </a>
            @endforeach
        </div>
    </div>
</x-indexLay>