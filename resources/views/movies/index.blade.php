<x-indexLay>
    <!-- HERO SECTION -->
    <div class="hero-section">
        <!-- HERO SLIDE -->
        <div class="hero-slide">
            @foreach ($topRated as $movie)
            @if ($loop->index < 1)
            <div class="owl-carousel carousel-nav-center" id="hero-carousel">
                <!-- SLIDE ITEM -->

                <div class="hero-slide-item">
                    <img src="{{'https://image.tmdb.org/t/p/original/'.$movie['backdrop_path']}}">
                    <div class="overlay"></div>
                    <div class="hero-slide-item-content">
                        <div class="item-content-wraper">
                            <div class="item-content-title top-down">
                                {{$movie['title']}}
                            </div>
                            <div class="movie-infos top-down delay-2">
                                <div class="movie-info">
                                    <i class="bx bxs-star"></i>
                                    <span>{{$movie['vote_average']}}</span>
                                </div>
                                <div class="movie-info">
                                    <span>{{\Carbon\Carbon::parse($movie['release_date'])->format('M d, Y')}}</span>
                                </div>
                                <div class="movie-info">
                                    @foreach ($movie['genre_ids'] as $genre)
                                        <span>{{$genres->get($genre)}}@if (!$loop->last),@endif </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="item-content-description top-down delay-4">
                                {{ $movie['overview'] }}
                            </div>
                            <div class="item-action top-down delay-6">
                                <a href="{{url('show', $movie['id'])}}" class="btn btn-hover">
                                    <i class="bx bxs-right-arrow"></i>
                                    <span>Watch now</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>                    
                @endif
                @endforeach
                <!-- END SLIDE ITEM -->  
            </div>
        </div>
        <!-- END HERO SLIDE -->
        <!-- TOP MOVIES SLIDE -->
        <div class="top-movies-slide">
            <div class="owl-carousel" id="top-movies-slide">
                <!-- MOVIE ITEM -->
                @foreach ($nowPlaying as $movie)
                <a href="{{url('show', $movie['id'])}}" class="movie-item">
                    <img src="{{'https://image.tmdb.org/t/p/w500/'.$movie['poster_path']}}" alt="">
                    <div class="movie-item-content">
                        <div class="movie-item-title">
                            {{$movie['title']}}
                        </div>
                        <div class="movie-infos">
                            <div class="movie-info">
                                <i class="bx bxs-star"></i>
                                <span>{{$movie['vote_average']}}</span>
                            </div>
                            <div class="movie-info">
                                <span>{{\Carbon\Carbon::parse($movie['release_date'])->format('M d, Y')}}</span>
                            </div>
                            <div class="movie-info">
                                @foreach ($movie['genre_ids'] as $genre)
                                    <span>{{$genres->get($genre)}}@if (!$loop->last),@endif </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        <!-- END TOP MOVIES SLIDE -->
    </div>
    <!-- END HERO SECTION -->

    <!-- LATEST MOVIES SECTION -->
    <div class="section">
        <div class="container">
            <div class="section-header">
                Trending
            </div>
            <div class="movies-slide carousel-nav-center owl-carousel">
                @foreach ($popularMovies as $movie)
                        <!-- MOVIE ITEM -->
                    <a href="{{url('show', $movie['id'])}}" class="movie-item">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$movie['poster_path']}}" alt="{{$movie['title']}}">
                        <div class="movie-item-content">
                            <div class="movie-item-title">
                                {{$movie['title']}}
                            </div>
                            <div class="movie-infos">
                                <div class="movie-info">
                                    <i class="bx bxs-star"></i>
                                    <span>{{$movie['vote_average']}}</span>
                                </div>
                                <div class="movie-info">
                                    <span>{{\Carbon\Carbon::parse($movie['release_date'])->format('M d, Y')}}</span>
                                </div>
                                <div class="movie-info">
                                    @foreach ($movie['genre_ids'] as $genre)
                                        <span>{{$genres->get($genre)}}@if (!$loop->last),@endif </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </a>
                        <!-- END MOVIE ITEM -->
                @endforeach
            </div>
        </div>
    </div>
    <!-- END LATEST MOVIES SECTION -->

    <!-- LATEST SERIES SECTION -->
    <div class="section">
        <div class="container">
            <div class="section-header">
                New Tv Show
            </div>
            <div class="movies-slide carousel-nav-center owl-carousel">
                @foreach ($newtv as $tv)
                <!-- MOVIE ITEM -->
                    <a href="{{url('showv', $tv['id'])}}" class="movie-item">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$tv['poster_path']}}" alt="{{$tv['name']}}">
                        <div class="movie-item-content">
                            <div class="movie-item-name">
                                {{$tv['name']}}
                            </div>
                            <div class="movie-infos">
                                <div class="movie-info">
                                    <i class="bx bxs-star"></i>
                                    <span>{{$tv['vote_average']}}</span>
                                </div>
                                <div class="movie-info">
                                    <span>{{\Carbon\Carbon::parse($tv['first_air_date'])->format('M d, Y')}}</span>
                                </div>
                                <div class="movie-info">
                                    @foreach ($tv['genre_ids'] as $genre)
                                        <span>{{$genresTv->get($genre)}}@if (!$loop->last),@endif </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </a>
                <!-- END MOVIE ITEM -->
                @endforeach
            </div>
        </div>
    </div>
    <!-- END LATEST SERIES SECTION -->

    <!-- LATEST CARTOONS SECTION -->
    <div class="section">
        <div class="container">
            <div class="section-header">
                New Animasi
            </div>
            <div class="movies-slide carousel-nav-center owl-carousel">
                @foreach ($anime as $ani)
                        <!-- MOVIE ITEM -->
                        <a href="{{url('show', $ani['id'])}}" class="movie-item">
                            <img src="{{'https://image.tmdb.org/t/p/w500/'.$ani['poster_path']}}" alt="{{$ani['title']}}">
                            <div class="movie-item-content">
                                <div class="movie-item-title">
                                    {{$ani['title']}}
                                </div>
                                <div class="movie-infos">
                                    <div class="movie-info">
                                        <i class="bx bxs-star"></i>
                                        <span>{{$ani['vote_average']}}</span>
                                    </div>
                                    <div class="movie-info">
                                        <span>{{\Carbon\Carbon::parse($ani['release_date'])->format('M d, Y')}}</span>
                                    </div>
                                    <div class="movie-info">
                                        @foreach ($ani['genre_ids'] as $genre)
                                            <span>{{$genres->get($genre)}}@if (!$loop->last),@endif </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </a>
                        <!-- END MOVIE ITEM -->
                @endforeach
            </div>
        </div>
    </div>
    <!-- END New Anime SECTION -->

</x-indexLay>