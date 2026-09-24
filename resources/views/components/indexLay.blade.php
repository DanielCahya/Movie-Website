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
                    <!-- Logout -->
                    <a href="{{ url('logout') }}" title="Logout" class="bg-white/10 hover:bg-white/20 text-white rounded-full p-2 transition-colors flex items-center justify-center">
                        <i class='bx bx-log-out text-xl'></i>
                    </a>
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