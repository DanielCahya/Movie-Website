<x-indexLay>
<div class="min-h-screen bg-brand-bg py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-brand-card rounded-3xl shadow-2xl overflow-hidden border border-gray-800 p-8 relative">
            
            @if(session('success'))
            <div class="absolute top-4 right-4 bg-green-500/20 backdrop-blur-md border border-green-500/50 text-green-100 px-4 py-2 rounded-xl text-sm shadow-lg z-10">
                {{ session('success') }}
            </div>
            @endif

            <div>
                <!-- Avatar & Edit Button -->
                <div class="flex justify-between items-start mb-6">
                    <div class="relative">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->username }}" class="w-32 h-32 rounded-full border-4 border-brand-card object-cover shadow-xl bg-gray-900">
                        @else
                            <div class="w-32 h-32 rounded-full border-4 border-brand-card shadow-xl bg-gray-800 flex items-center justify-center text-4xl text-brand font-bold uppercase">
                                {{ substr($user->username, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    
                    @auth
                        @if(Auth::user()->id === $user->id)
                            <a href="{{ route('profile.edit') }}" class="bg-gray-800 hover:bg-gray-700 text-white border border-gray-700 px-6 py-2.5 rounded-full font-medium transition-colors shadow-lg flex items-center gap-2">
                                <i class='bx bx-edit-alt'></i> Edit Profile
                            </a>
                        @else
                            <!-- Follow Button Placeholder for Phase 2 -->
                            <button class="bg-brand hover:bg-cyan-400 text-white px-8 py-2.5 rounded-full font-bold transition-colors shadow-lg shadow-brand/30">
                                Follow
                            </button>
                        @endif
                    @endauth
                </div>

                <!-- User Info -->
                <div>
                    <h1 class="text-3xl font-bold text-white">{{ $user->username }}</h1>
                    <p class="text-gray-400 text-sm mt-1">Joined {{ $user->created_at->format('F Y') }}</p>
                    
                    <!-- Bio -->
                    <div class="mt-6">
                        <h3 class="text-lg font-medium text-white mb-2">About</h3>
                        <p class="text-gray-300 leading-relaxed bg-gray-900/50 p-4 rounded-2xl border border-gray-800">
                            {{ $user->bio ?: "This user hasn't written a bio yet." }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Future Tabs (Watchlist, Forums) -->
        <div class="mt-8">
            <div class="flex border-b border-gray-800 gap-8">
                <button class="pb-4 text-brand border-b-2 border-brand font-medium">Recent Activity</button>
                <button class="pb-4 text-gray-500 hover:text-gray-300 font-medium transition-colors">Watchlist (Coming Soon)</button>
            </div>
            
            <div class="py-8 text-center text-gray-500">
                <i class='bx bx-ghost text-6xl mb-4 opacity-50'></i>
                <p>Nothing to see here yet.</p>
            </div>
        </div>

    </div>
</div>
</x-indexLay>
