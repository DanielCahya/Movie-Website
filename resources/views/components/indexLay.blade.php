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

    <title>Galiwe</title>
</head>
<body class="bg-brand-bg text-white font-sans antialiased min-h-screen flex flex-col">

    <!-- Navigation Bar -->
    <nav class="fixed w-full z-50 bg-brand-bg/90 backdrop-blur-md border-b border-white/5 transition-all duration-300">
        <div class="w-full px-4 sm:px-8 lg:px-12 xl:px-16">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('home') }}" class="text-2xl font-bold tracking-tighter text-white">
                        Ga<span class="text-brand">li</span>we
                    </a>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex flex-grow justify-center space-x-8">
                    <a href="{{ url('home') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Home</a>
                    <a href="{{ url('tvshow') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Tv Shows</a>
                    <a href="{{ url('movieList') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Movies</a>
                    <a href="{{ url('animation') }}" class="text-brand px-3 py-2 text-base font-medium">Anime</a>
                    @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin']))
                    <a href="{{ url('admin') }}" class="text-gray-300 hover:text-white px-3 py-2 text-base font-medium transition-colors">Admin</a>
                    @endif
                </div>
                
                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Search Bar (Livewire component) -->
                    <div class="hidden md:block">
                        <livewire:search>
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
                            <span class="font-medium hidden md:block">{{ Auth::user()->username }}</span>
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
                            
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-colors border-b border-gray-700/50">
                                <i class='bx bx-user text-lg text-brand'></i> Profile
                            </a>
                            
                            <a href="{{ url('logout') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 transition-colors">
                                <i class='bx bx-log-out text-lg'></i> Sign Out
                            </a>
                        </div>
                    </div>
                    @else
                    <!-- Login / Register -->
                    <div class="flex items-center space-x-4 ml-2">
                        <a href="{{ url('login') }}" class="text-gray-300 hover:text-white text-base font-medium transition-colors">Login</a>
                        <a href="{{ url('login?register=true') }}" class="bg-brand hover:bg-cyan-400 text-white px-5 py-2 rounded-full text-base font-bold transition-colors shadow-lg shadow-brand/20">Sign Up</a>
                    </div>
                    @endauth
                </div>
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