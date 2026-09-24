<x-indexLay>
    <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16 py-12 relative z-20">
        
        <!-- MAIN MOVIE DETAIL SECTION -->
        <div class="flex flex-col md:flex-row gap-8 lg:gap-16">
            
            <!-- Left: Poster -->
            <div class="flex-none w-full md:w-1/3 lg:w-[400px]">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl shadow-brand-bg/50">
                    <img src="{{ $popularMovies['poster_path'] ? 'https://image.tmdb.org/t/p/original/'.$popularMovies['poster_path'] : 'https://via.placeholder.com/500x750' }}" alt="Movie Poster" class="w-full h-auto object-cover">
                </div>
            </div>

            <!-- Right: Movie Details -->
            <div class="flex-grow flex flex-col justify-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white mb-6 tracking-tight">
                    {{$popularMovies['title']}}
                </h1>

                <!-- Badges & Tags -->
                <div class="flex flex-wrap items-center gap-3 mb-8">
                    <!-- Genres -->
                    <div class="flex flex-wrap gap-2 mr-4">
                        @foreach ($popularMovies['genres'] as $genre)
                            <span class="px-4 py-1.5 bg-brand-card/80 border border-gray-600/50 rounded-full text-sm text-gray-300 font-medium hover:border-brand transition-colors cursor-default">
                                {{$genre['name']}}
                            </span>
                        @endforeach
                    </div>

                    <!-- TMDb Rating Badge -->
                    <div class="flex items-center space-x-1.5 bg-yellow-500/10 border border-yellow-500/20 px-3 py-1.5 rounded-lg text-yellow-500 font-bold text-sm">
                        <i class='bx bxs-star'></i>
                        <span>{{$popularMovies['vote_average']}}/10</span>
                    </div>

                    <!-- Release / Status Badge -->
                    <div class="bg-gray-800 border border-gray-600 px-3 py-1.5 rounded-lg text-gray-300 font-bold text-sm">
                        {{ \Carbon\Carbon::parse($popularMovies['release_date'])->format('M d, Y') }}
                    </div>
                </div>

                <!-- Overview -->
                <div class="text-gray-300 text-lg leading-relaxed mb-8 max-w-3xl">
                    {{ $popularMovies['overview'] }}
                </div>

                <!-- Action Buttons & Trailer Alpine Component -->
                <div x-data="{ isOpen: false }">
                    <div class="flex flex-wrap gap-4 mb-12">
                        @if (count($popularMovies['videos']['results']) > 0)
                        <button type="button" @click.prevent="isOpen = true" class="flex items-center justify-center bg-brand-dark hover:bg-teal-600 text-white font-semibold py-3 px-8 rounded-full transition-colors shadow-lg shadow-brand-dark/30">
                            Watch Trailer <i class='bx bx-play-circle text-2xl ml-2'></i>
                        </button>
                        @endif
                        
                        <button class="flex items-center justify-center bg-transparent hover:bg-white/5 border border-gray-500 text-white font-semibold py-3 px-8 rounded-full transition-colors">
                            To Watchlist <i class='bx bx-plus text-xl ml-2'></i>
                        </button>
                    </div>

                    <!-- Trailer Modal (Alpine.js) -->
                    <div x-show="isOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
                        <div class="w-full max-w-5xl bg-brand-bg rounded-2xl overflow-hidden shadow-2xl relative" @click.away="isOpen = false">
                            <button @click="isOpen = false" class="absolute -top-12 right-0 text-white hover:text-brand transition-colors text-4xl">
                                &times;
                            </button>
                            <div class="relative pt-[56.25%] w-full">
                                <template x-if="isOpen">
                                    <iframe class="absolute top-0 left-0 w-full h-full" src="https://www.youtube.com/embed/{{ $popularMovies['videos']['results'][0]['key'] }}?autoplay=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Crew (Director, Writer, etc) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 pt-8 border-t border-gray-800">
                    @foreach (array_slice($popularMovies['credits']['crew'], 0, 3) as $crew)
                    <div>
                        <p class="text-white font-semibold">{{ $crew['name'] }}</p>
                        <p class="text-gray-400 text-sm">{{ $crew['job'] }}</p>
                    </div>
                    @endforeach
                </div>
                
            </div>
        </div>

        <!-- CAST SECTION -->
        <div class="mt-24 pt-12 border-t border-gray-800">
            <h2 class="text-3xl font-bold text-white mb-8 border-l-4 border-brand pl-4">Top Cast</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                @foreach (array_slice($popularMovies['credits']['cast'], 0, 10) as $cast)
                    <div class="group">
                        <div class="relative rounded-xl overflow-hidden aspect-[2/3] bg-brand-card shadow-lg mb-3">
                            <img src="{{ $cast['profile_path'] ? 'https://image.tmdb.org/t/p/w500/'.$cast['profile_path'] : 'https://ui-avatars.com/api/?name='.urlencode($cast['name']).'&background=0D8ABC&color=fff&size=500' }}" alt="{{ $cast['name'] }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </div>
                        <h3 class="text-white font-semibold text-sm truncate" title="{{ $cast['name'] }}">{{ $cast['name'] }}</h3>
                        <p class="text-brand text-xs truncate" title="{{ $cast['character'] }}">{{ $cast['character'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- MOVIE PICTURES / BACKDROPS -->
        @if(isset($popularMovies['images']['backdrops']) && count($popularMovies['images']['backdrops']) > 0)
        <div class="mt-24 pt-12 border-t border-gray-800">
            <h2 class="text-3xl font-bold text-white mb-8 border-l-4 border-brand pl-4">Gallery</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach (array_slice($popularMovies['images']['backdrops'], 0, 6) as $image)
                    <div class="rounded-xl overflow-hidden aspect-video bg-brand-card shadow-lg cursor-pointer hover:ring-2 hover:ring-brand transition-all">
                        <img src="{{ 'https://image.tmdb.org/t/p/w500/'.$image['file_path'] }}" alt="Backdrop" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</x-indexLay>