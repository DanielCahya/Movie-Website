<x-indexLay>
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="flex justify-between items-center mb-8 border-b border-gray-700/50 pb-4">
        <h1 class="text-3xl font-bold text-white border-l-4 border-brand pl-4">Community Forums</h1>
        @auth
            <a href="{{ route('forum.create') }}" class="bg-brand hover:bg-teal-400 text-brand-dark font-bold py-2 px-6 rounded-full transition-colors shadow-lg shadow-brand/20 flex items-center gap-2">
                <i class='bx bx-plus text-lg'></i> New Thread
            </a>
        @else
            <a href="{{ route('login') }}" class="text-brand hover:text-white transition-colors">Login to Post</a>
        @endauth
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Categories -->
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-xl font-semibold text-gray-300 mb-4">Categories</h2>
            @foreach($categories as $category)
                <a href="{{ route('forum.category', $category->slug) }}" class="block bg-brand-card hover:bg-gray-800/80 border border-gray-700/50 rounded-xl p-6 transition-all duration-300 shadow-lg hover:shadow-brand/5 group relative overflow-hidden">
                    <!-- Subtle glow effect on hover -->
                    <div class="absolute inset-0 bg-gradient-to-r from-brand/0 via-brand/5 to-brand/0 opacity-0 group-hover:opacity-100 transition-opacity duration-500 transform -translate-x-full group-hover:translate-x-full"></div>
                    
                    <div class="flex justify-between items-center relative z-10">
                        <div>
                            <h3 class="text-xl font-bold text-white group-hover:text-brand transition-colors">{{ $category->name }}</h3>
                            <p class="text-gray-400 text-sm mt-1">{{ $category->description }}</p>
                        </div>
                        <div class="text-center bg-gray-900 rounded-lg p-3 border border-gray-700/30 group-hover:border-brand/30 transition-colors">
                            <span class="block text-2xl font-bold text-brand leading-none">{{ $category->threads_count }}</span>
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider mt-1 block">Threads</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Recent Threads Sidebar -->
        <div class="space-y-4">
            <h2 class="text-xl font-semibold text-gray-300 mb-4">Recent Discussions</h2>
            <div class="bg-brand-card border border-gray-700/50 rounded-xl p-4 shadow-lg">
                @if($recentThreads->count() > 0)
                    <ul class="space-y-4">
                        @foreach($recentThreads as $thread)
                            <li class="border-b border-gray-700/50 last:border-0 pb-4 last:pb-0 group">
                                <a href="{{ route('forum.thread', $thread->slug) }}" class="block hover:text-brand transition-colors text-white font-medium line-clamp-2 mb-2">
                                    {{ $thread->title }}
                                </a>
                                <div class="flex items-center text-xs text-gray-500 gap-3">
                                    <span class="bg-gray-800 px-2 py-1 rounded text-gray-400 whitespace-nowrap">{{ $thread->category->name }}</span>
                                    <span class="flex items-center gap-1">
                                        <i class='bx bx-user text-gray-600'></i> 
                                        <a href="{{ route('profile.show', $thread->user->username) }}" class="hover:text-white transition-colors">{{ $thread->user->username }}</a>
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-8 opacity-50">
                        <i class='bx bx-message-alt-x text-4xl text-gray-500 mb-2'></i>
                        <p class="text-gray-400 text-sm">No recent discussions yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-indexLay>
