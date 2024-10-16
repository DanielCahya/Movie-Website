<x-indexLay>
    <!-- HERO SECTION -->
    <div class="hero-section">
        <!-- HERO SLIDE -->
        <div class="hero-slide">
            @foreach ($topRated as $topRated)
            @if ($loop->index < 1)
            <div class="owl-carousel carousel-nav-center" id="hero-carousel">
                <!-- SLIDE ITEM -->

                <div class="hero-slide-item">
                    <img src="{{'https://image.tmdb.org/t/p/original/'.$topRated['backdrop_path']}}">
                    <div class="overlay"></div>
                    <div class="hero-slide-item-content">
                        <div class="item-content-wraper">
                            <div class="item-content-title top-down">
                                {{$topRated['title']}}
                            </div>
                            <div class="movie-infos top-down delay-2">
                                <div class="movie-info">
                                    <i class="bx bxs-star"></i>
                                    <span>{{$topRated['vote_average']}}</span>
                                </div>
                                <div class="movie-info">
                                    <span>{{\Carbon\Carbon::parse($topRated['release_date'])->format('M d, Y')}}</span>
                                </div>
                                <div class="movie-info">
                                    @foreach ($topRated['genre_ids'] as $genre)
                                        <span>{{$genres->get($genre)}}@if (!$loop->last),@endif </span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="item-content-description top-down delay-4">
                                {{ $topRated['overview'] }}
                            </div>
                            <div class="item-action top-down delay-6">
                                <a href="{{url('show', $topRated['id'])}}" class="btn btn-hover">
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
                @foreach ($nowPlaying as $nowPlaying)
                <a href="{{url('show', $nowPlaying['id'])}}" class="movie-item">
                    <img src="{{'https://image.tmdb.org/t/p/w500/'.$nowPlaying['poster_path']}}" alt="">
                    <div class="movie-item-content">
                        <div class="movie-item-title">
                            {{$nowPlaying['title']}}
                        </div>
                        <div class="movie-infos">
                            <div class="movie-info">
                                <i class="bx bxs-star"></i>
                                <span>{{$nowPlaying['vote_average']}}</span>
                            </div>
                            <div class="movie-info">
                                <span>{{\Carbon\Carbon::parse($nowPlaying['release_date'])->format('M d, Y')}}</span>
                            </div>
                            <div class="movie-info">
                                @foreach ($nowPlaying['genre_ids'] as $genre)
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
                @foreach ($popularMovies as $popularMovies)
                        <!-- MOVIE ITEM -->
                    <a href="{{url('show', $popularMovies['id'])}}" class="movie-item">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$popularMovies['poster_path']}}" alt="{{$popularMovies['title']}}">
                        <div class="movie-item-content">
                            <div class="movie-item-title">
                                {{$popularMovies['title']}}
                            </div>
                            <div class="movie-infos">
                                <div class="movie-info">
                                    <i class="bx bxs-star"></i>
                                    <span>{{$popularMovies['vote_average']}}</span>
                                </div>
                                <div class="movie-info">
                                    <span>{{\Carbon\Carbon::parse($popularMovies['release_date'])->format('M d, Y')}}</span>
                                </div>
                                <div class="movie-info">
                                    @foreach ($popularMovies['genre_ids'] as $genre)
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
                @foreach ($newtv as $newtv)
                <!-- MOVIE ITEM -->
                    <a href="{{url('showv', $newtv['id'])}}" class="movie-item">
                        <img src="{{'https://image.tmdb.org/t/p/w500/'.$newtv['poster_path']}}" alt="{{$newtv['name']}}">
                        <div class="movie-item-content">
                            <div class="movie-item-name">
                                {{$newtv['name']}}
                            </div>
                            <div class="movie-infos">
                                <div class="movie-info">
                                    <i class="bx bxs-star"></i>
                                    <span>{{$newtv['vote_average']}}</span>
                                </div>
                                <div class="movie-info">
                                    <span>{{\Carbon\Carbon::parse($newtv['first_air_date'])->format('M d, Y')}}</span>
                                </div>
                                <div class="movie-info">
                                    @foreach ($newtv['genre_ids'] as $genre)
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
                @foreach ($anime as $anime)
                        <!-- MOVIE ITEM -->
                        <a href="{{url('show', $anime['id'])}}" class="movie-item">
                            <img src="{{'https://image.tmdb.org/t/p/w500/'.$anime['poster_path']}}" alt="{{$anime['title']}}">
                            <div class="movie-item-content">
                                <div class="movie-item-title">
                                    {{$anime['title']}}
                                </div>
                                <div class="movie-infos">
                                    <div class="movie-info">
                                        <i class="bx bxs-star"></i>
                                        <span>{{$anime['vote_average']}}</span>
                                    </div>
                                    <div class="movie-info">
                                        <span>{{\Carbon\Carbon::parse($anime['release_date'])->format('M d, Y')}}</span>
                                    </div>
                                    <div class="movie-info">
                                        @foreach ($anime['genre_ids'] as $genre)
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