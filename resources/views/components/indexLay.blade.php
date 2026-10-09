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
                <div class="hidden md:flex flex-grow justify-center space-x-8">
                    <a wire:navigate.hover href="{{ url('home') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Home</a>
                    <a wire:navigate.hover href="{{ url('tvshow') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Tv Shows</a>
                    <a wire:navigate.hover href="{{ url('movieList') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Movies</a>
                    <a wire:navigate.hover href="{{ url('animation') }}" class="text-brand px-3 py-2 text-base font-medium">Anime</a>
                    <a wire:navigate.hover href="{{ route('forum.index') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Community</a>
                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin']))
                    <a wire:navigate.hover href="{{ url('admin') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Admin</a>
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
                            <a wire:navigate.hover href="{{ url('logout') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                                <i class='bx bx-log-out text-lg'></i> Sign Out
                            </a>
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
            <div class="px-4 py-4 space-y-3 flex flex-col">
                <livewire:search />
                
                <a wire:navigate.hover href="{{ url('home') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium rounded-md hover:bg-white/5 transition-colors">Home</a>
                <a wire:navigate.hover href="{{ url('tvshow') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium rounded-md hover:bg-white/5 transition-colors">Tv Shows</a>
                <a wire:navigate.hover href="{{ url('movieList') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium rounded-md hover:bg-white/5 transition-colors">Movies</a>
                <a wire:navigate.hover href="{{ url('animation') }}" class="text-brand px-3 py-2 text-base font-medium rounded-md hover:bg-white/5 transition-colors">Anime</a>
                <a wire:navigate.hover href="{{ route('forum.index') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium rounded-md hover:bg-white/5 transition-colors">Community</a>
                
                @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin']))
                <a wire:navigate.hover href="{{ url('admin') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium rounded-md hover:bg-white/5 transition-colors">Admin</a>
                @endif
                
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

    <livewire:scripts />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="{{ asset('js/app.js') }}"></script>
</body>
</html>
