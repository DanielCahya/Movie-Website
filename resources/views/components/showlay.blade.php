<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
        <!-- OWL CAROUSEL -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" integrity="sha512-tS3S5qG0BlhnQROyJXvNjeEM4UpMXHrQfTGmbQ1gKmelCxlSEBUaxhRBj/EFTzpbP4RVSrpEikbmdJobCvhE3g==" crossorigin="anonymous" />
        <!-- BOX ICONS -->
        <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
        <!-- APP CSS -->
        <link rel="stylesheet" href="/css/grid.css">
        <link rel="stylesheet" href="/css/appme.css">
        <link rel="stylesheet" href="/css/index.css">
        <link rel="stylesheet" href="/css/main.css">
        <livewire:styles>
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>

        <title>Galiwe</title>
    </head>
    <body>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <!-- OWL CAROUSEL -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js" integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw==" crossorigin="anonymous"></script>
        <!-- APP SCRIPT -->
        <script type="text/javascript" src="/js/appme.js"></script>
        <script type="text/javascript" src="/js/index.js"></script>
        <div class="nav-wrapper">
            <div class="container">
                <div class="nav">
                    <a href="#" class="logo">
                        </i>Ga<span class="main-color">li</span>we
                    </a>
                    <ul class="nav-menu" id="nav-menu">
                        <li><a href="{{url('home')}}">Home</a></li>
                        <li><a href="{{url('tvshow')}}">Tv Show</a></li>
                        <li><a href="{{url('movieList')}}">Movies</a></li>
                        <li><a href="{{url('animation')}}">Anime</a></li>
                        
                        <li>
                            <a href="{{url('logout')}}" class="btn btn-hover">
                                <span>logout</span>
                            </a>
                        </li>
                    </ul>
                    <!-- MOBILE MENU TOGGLE -->
                    <div class="hamburger-menu" id="hamburger-menu">
                        <div class="hamburger"></div>
                    </div>
                </div>
            </div>
        </div>
        {{ $slot }}

        @yield('content')
        <footer class="border border-t border-gray-800">
            <div class="container mx-auto text-sm px-4 py-6">
                Powered by <a href="https://www.themoviedb.org/documentation/api" class="underline hover:text-gray-300">TMDb API</a>
            </div>
        </footer>
        <livewire:scripts>
        @yield('scripts')
    </body>
</html>