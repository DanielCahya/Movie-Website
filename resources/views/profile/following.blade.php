<x-indexLay>
<div class="bg-brand-bg min-h-[calc(100vh-4rem)] flex">
    <!-- Sidebar -->
    @include('profile.partials.sidebar')

    <!-- Main Content -->
    <main class="flex-1 p-6 lg:p-12 overflow-y-auto">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-1">Followed Users</h1>
                    <p class="text-sm text-gray-500">{{ $followedUsers->count() }} members you follow.</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <i class='bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500'></i>
                        <input type="text" placeholder="Search users" class="bg-brand-card border-none rounded-md pl-10 pr-4 py-2 text-sm text-white placeholder-gray-500 focus:ring-1 focus:ring-brand w-64">
                    </div>
                    <div class="flex bg-brand-card rounded-md p-1">
                        <button class="px-3 py-1.5 text-xs font-semibold text-white bg-white/10 rounded shadow-sm">Recent</button>
                        <button class="px-3 py-1.5 text-xs font-semibold text-gray-400 hover:text-white transition-colors">A-Z</button>
                    </div>
                </div>
            </div>

            <!-- Content Container -->
            @if($followedUsers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($followedUsers as $followed)
                        <div class="bg-brand-card rounded-xl p-4 flex items-center justify-between border border-white/5">
                            <a href="{{ route('profile.show', $followed->username) }}" class="flex items-center gap-3">
                                @if($followed->avatar)
                                    <img src="{{ asset('storage/' . $followed->avatar) }}" alt="{{ $followed->username }}" class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-brand-bg flex items-center justify-center text-brand font-bold uppercase border border-white/5">
                                        {{ substr($followed->username, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <h3 class="font-bold text-white">{{ $followed->username }}</h3>
                                    <p class="text-xs text-gray-400">Joined {{ $followed->created_at->format('M Y') }}</p>
                                </div>
                            </a>
                            <form action="{{ route('follow.toggle', $followed->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded text-xs font-semibold transition-colors">Unfollow</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-brand-card rounded-xl p-16 flex flex-col items-center justify-center text-center border border-white/5">
                    <h3 class="text-white font-bold mb-2 text-lg">Not following any users yet.</h3>
                    <p class="text-gray-500 text-sm">Visit a member's profile and tap Follow.</p>
                </div>
            @endif
        </div>
    </main>
</div>
</x-indexLay>
