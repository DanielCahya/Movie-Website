<aside class="w-64 flex-shrink-0 bg-brand-bg border-r border-white/5 hidden md:block border-none">
    <nav class="p-4 space-y-1">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('profile.edit') ? 'bg-white/5 text-white border-l-2 border-brand' : 'text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent' }}">
            <i class='bx bx-user text-xl'></i> Edit Profile
        </a>
        <a href="{{ route('profile.watchlist') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('profile.watchlist') ? 'bg-white/5 text-white border-l-2 border-brand' : 'text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent' }}">
            <i class='bx bx-bookmark text-xl'></i> Watchlist
        </a>
        <a href="{{ route('profile.following') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('profile.following') ? 'bg-white/5 text-white border-l-2 border-brand' : 'text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent' }}">
            <i class='bx bx-group text-xl'></i> Followed Users
        </a>
        <a href="{{ route('profile.blocked') }}" class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('profile.blocked') ? 'bg-white/5 text-white border-l-2 border-brand' : 'text-gray-400 hover:text-white hover:bg-white/5 transition-colors border-l-2 border-transparent' }}">
            <i class='bx bx-block text-xl'></i> Blocked Users
        </a>
    </nav>
</aside>
