<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Boxicons for standard icons if needed -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Tailwind CSS (via CDN for immediate rendering without Webpack) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#06b6d4', // Cyan accent
                            dark: '#0f766e',    // Teal button
                            bg: '#0f172a',      // Dark slate background
                            card: '#1e293b',    // Slightly lighter card
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <livewire:styles />
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- GSAP for Animations -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/ScrollTrigger.min.js"></script>

    <!-- Global GSAP Animations -->
    <script>
        document.addEventListener("DOMContentLoaded", (event) => {
            gsap.registerPlugin(ScrollTrigger);
        });
    </script>
    <style>
        /* Aggressive Spotlight Effect for Movie Cards */
        .spotlight-group:has(.spotlight-card:hover) .spotlight-card:not(:hover) {
            transform: scale(0.85) !important;
            opacity: 0.3 !important;
        }
        .spotlight-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            will-change: transform, opacity;
        }
        .spotlight-card:hover {
            transform: scale(1.2) translateY(-12px) !important;
            opacity: 1 !important;
            z-index: 50 !important;
        }
        /* Fix clipping in scrollable carousels */
        .spotlight-scroll-container {
            padding-top: 2rem !important;
            padding-bottom: 3rem !important;
            margin-top: -2rem !important;
            margin-bottom: -3rem !important;
            clip-path: inset(-3rem -3rem -3rem -3rem);
        }

        /* ── Navbar Active Spotlight ── */
        .nav-link {
            position: relative;
            padding: 0.5rem 0.75rem;
            font-size: 0.9375rem;
            font-weight: 500;
            color: rgba(209,213,219,1);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: color 0.2s ease;
        }
        .nav-link::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 0.5rem;
            background: rgba(6, 182, 212, 0);
            transition: background 0.25s ease;
            pointer-events: none;
        }
        /* Glowing underline bar */
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 70%;
            height: 2px;
            border-radius: 9999px;
            background: linear-gradient(90deg, transparent, #06b6d4, transparent);
            box-shadow: 0 0 8px 2px rgba(6,182,212,0.6);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .nav-link:hover {
            color: #ffffff;
        }
        .nav-link:hover::before {
            background: rgba(6, 182, 212, 0.08);
        }
        .nav-link:hover::after {
            transform: translateX(-50%) scaleX(1);
        }
        /* Active state */
        .nav-link.active {
            color: #ffffff !important;
            font-weight: 600;
        }
        .nav-link.active::before {
            background: rgba(6, 182, 212, 0.13);
            box-shadow: 0 0 18px 4px rgba(6,182,212,0.13);
        }
        .nav-link.active::after {
            transform: translateX(-50%) scaleX(1);
            background: linear-gradient(90deg, transparent, #06b6d4, transparent);
            box-shadow: 0 0 12px 3px rgba(6,182,212,0.75);
        }
        /* Mobile active state */
        .nav-link-mobile {
            display: block;
            padding: 0.5rem 0.75rem;
            font-size: 0.9375rem;
            font-weight: 500;
            border-radius: 0.5rem;
            color: rgba(209,213,219,1);
            transition: color 0.2s, background 0.2s;
        }
        .nav-link-mobile:hover {
            color: #fff;
            background: rgba(255,255,255,0.05);
        }
        .nav-link-mobile.active {
            color: #06b6d4 !important;
            background: rgba(6,182,212,0.10) !important;
            font-weight: 600;
            border-left: 3px solid #06b6d4;
            padding-left: calc(0.75rem - 3px);
        }
    </style>
    <title>@yield('title', 'Galiwe')</title>
    <meta name="description" content="@yield('meta_description', 'Discover and track the best movies and TV shows on Galiwe.')">
    <meta name="view-transition" content="same-origin" />
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Galiwe')">
    <meta property="og:description" content="@yield('meta_description', 'Discover and track the best movies and TV shows on Galiwe.')">
    <meta property="og:image" content="@yield('og_image', asset('default-og.jpg'))">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'Galiwe')">
    <meta property="twitter:description" content="@yield('meta_description', 'Discover and track the best movies and TV shows on Galiwe.')">
    <meta property="twitter:image" content="@yield('og_image', asset('default-og.jpg'))">
</head>
<body class="bg-brand-bg text-white font-sans antialiased min-h-screen flex flex-col">

    <!-- Navigation Bar -->
    <nav x-data="{ mobileMenuOpen: false }" class="fixed top-0 left-0 w-full z-50 bg-brand-bg/60 backdrop-blur-xl border-b border-white/10 transition-all duration-300 shadow-[0_4px_30px_rgba(0,0,0,0.1)]">
        <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16">
            <div class="flex items-center justify-between h-16">
                <!-- Mobile Menu Button & Logo -->
                <div class="flex items-center gap-3 md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-300 hover:text-white focus:outline-none">
                        <i class='bx text-2xl' :class="mobileMenuOpen ? 'bx-x' : 'bx-menu'"></i>
                    </button>
                    <a wire:navigate.hover href="{{ url('home') }}" class="text-xl font-bold tracking-tighter text-white">
                        Ga<span class="text-brand">li</span>we
                    </a>
                </div>

                <!-- Desktop Logo -->
                <div class="hidden md:flex flex-shrink-0 items-center">
                    <a wire:navigate.hover href="{{ url('home') }}" class="text-2xl font-bold tracking-tighter text-white">
                        Ga<span class="text-brand">li</span>we
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex flex-grow justify-center space-x-2">
                    <a wire:navigate.hover href="{{ url('home') }}" class="nav-link {{ request()->is('home') || request()->is('/') ? 'active' : '' }}">Home</a>
                    <a wire:navigate.hover href="{{ url('tvshow') }}" class="nav-link {{ request()->is('tvshow') ? 'active' : '' }}">TV Shows</a>
                    <a wire:navigate.hover href="{{ url('movieList') }}" class="nav-link {{ request()->is('movieList') ? 'active' : '' }}">Movies</a>
                    <a wire:navigate.hover href="{{ url('animation') }}" class="nav-link {{ request()->is('animation') ? 'active' : '' }}">Anime</a>
                    <a wire:navigate.hover href="{{ route('forum.index') }}" class="nav-link {{ request()->is('forum*') ? 'active' : '' }}">Community</a>
                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin']))
                    <a wire:navigate.hover href="{{ url('admin') }}" class="nav-link {{ request()->is('admin*') ? 'active' : '' }}">Admin</a>
                    @endif
                </div>
                
                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Search Bar (Livewire component) -->
                    <div class="hidden md:block">
                        <livewire:search />
                    </div>
                    
                    @auth
                    <!-- User Dropdown Menu -->
                    <div class="relative ml-4 pl-4 border-l border-white/10" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 text-white hover:text-brand transition-colors group focus:outline-none">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-9 h-9 rounded-full border border-white/20 group-hover:border-brand transition-colors object-cover shadow-sm">
                            @else
                                <div class="w-9 h-9 rounded-full bg-brand-card border border-white/20 group-hover:border-brand flex items-center justify-center text-sm font-bold text-brand transition-colors uppercase shadow-sm">
                                    {{ substr(Auth::user()->username, 0, 1) }}
                                </div>
                            @endif
                            <i class='bx bx-chevron-down text-xl text-gray-400 group-hover:text-brand transition-colors' :class="{'rotate-180': open}"></i>
                        </button>

                        <!-- Dropdown Content -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-3 w-52 bg-brand-card border border-gray-700 rounded-xl shadow-2xl overflow-hidden z-50 py-1"
                             style="display: none;">
                            
                            <a wire:navigate.hover href="{{ route('profile.show', Auth::user()->username) }}" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-colors border-b border-gray-700/50">
                                <i class='bx bx-user-circle text-lg text-brand'></i> Public Profile
                            </a>
                            <a wire:navigate.hover href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-colors border-b border-gray-700/50">
                                <i class='bx bx-cog text-lg text-brand'></i> Settings
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors text-left">
                                    <i class='bx bx-log-out text-lg'></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <!-- Login / Register -->
                    <div class="flex items-center space-x-4 ml-2">
                        <a wire:navigate.hover href="{{ url('login') }}" class="text-gray-300 hover:text-white text-base font-medium transition-colors">Login</a>
                        <a wire:navigate.hover href="{{ url('login?register=true') }}" class="bg-brand hover:bg-cyan-400 text-white px-5 py-2 rounded-full text-base font-bold transition-colors shadow-lg shadow-brand/20">Sign Up</a>
                    </div>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden absolute top-16 left-0 w-full bg-brand-card border-b border-white/10 shadow-2xl z-40"
             style="display: none;">
            <div class="px-4 py-4 space-y-1 flex flex-col">
                <livewire:search />
                <div class="pt-2 space-y-1">
                    <a wire:navigate.hover href="{{ url('home') }}" class="nav-link-mobile {{ request()->is('home') || request()->is('/') ? 'active' : '' }}">Home</a>
                    <a wire:navigate.hover href="{{ url('tvshow') }}" class="nav-link-mobile {{ request()->is('tvshow') ? 'active' : '' }}">TV Shows</a>
                    <a wire:navigate.hover href="{{ url('movieList') }}" class="nav-link-mobile {{ request()->is('movieList') ? 'active' : '' }}">Movies</a>
                    <a wire:navigate.hover href="{{ url('animation') }}" class="nav-link-mobile {{ request()->is('animation') ? 'active' : '' }}">Anime</a>
                    <a wire:navigate.hover href="{{ route('forum.index') }}" class="nav-link-mobile {{ request()->is('forum*') ? 'active' : '' }}">Community</a>
                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin']))
                    <a wire:navigate.hover href="{{ url('admin') }}" class="nav-link-mobile {{ request()->is('admin*') ? 'active' : '' }}">Admin</a>
                    @endif
                </div>
                
                @guest
                <div class="pt-4 mt-2 border-t border-white/10 flex flex-col gap-3">
                    <a wire:navigate.hover href="{{ url('login') }}" class="text-center text-gray-300 hover:text-white text-base font-medium transition-colors py-2">Login</a>
                    <a wire:navigate.hover href="{{ url('login?register=true') }}" class="text-center bg-brand hover:bg-cyan-400 text-white px-5 py-2 rounded-full text-base font-bold transition-colors">Sign Up</a>
                </div>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow pt-16">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-gray-800 bg-brand-bg/95 backdrop-blur-md text-gray-400 py-10 relative z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Branding & Legal Links -->
                <div class="flex flex-col items-center md:items-start text-center md:text-left space-y-2">
                    <div class="flex items-center space-x-2">
                        <span class="text-xl font-black tracking-wider text-white">GALI<span class="text-brand">WE</span></span>
                    </div>
                    <p class="text-xs text-gray-500">© {{ date('Y') }} MovieWebsite. All rights reserved.</p>
                    <div class="flex items-center space-x-4 text-xs pt-1">
                        <a href="{{ route('privacy') }}" class="hover:text-brand transition-colors">Privacy Policy</a>
                        <span>•</span>
                        <a href="{{ route('terms') }}" class="hover:text-brand transition-colors">Terms of Service</a>
                        <span>•</span>
                        <a href="{{ url('sitemap.xml') }}" class="hover:text-brand transition-colors">Sitemap</a>
                    </div>
                </div>

                <!-- TMDB Attribution -->
                <div class="flex items-center space-x-4 bg-brand-card/60 border border-gray-800 rounded-xl p-4 max-w-md">
                    <img src="https://www.themoviedb.org/assets/2/v4/logos/v2/blue_square_2-d537fb228cf3ded904ef09b136fe3fec72548ebc1fea3fbbd1ad9e36364db38b.svg" alt="TMDB Logo" class="h-8 w-auto flex-none">
                    <p class="text-[11px] text-gray-400 leading-tight">
                        This product uses the TMDB API but is not endorsed or certified by TMDB.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <livewire:scripts />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="{{ asset('js/app.js') }}"></script>
</body>
</html>
