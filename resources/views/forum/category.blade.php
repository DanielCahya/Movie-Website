<x-indexLay>
<div class="container mx-auto px-4 py-8 max-w-6xl">
    <div class="mb-6 flex items-center text-sm text-gray-400 gap-2">
        <a href="{{ route('forum.index') }}" class="hover:text-brand transition-colors flex items-center gap-1"><i class='bx bx-home'></i> Forums</a>
        <i class='bx bx-chevron-right text-gray-600'></i>
        <span class="text-white">{{ $category->name }}</span>
    </div>

    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 border-b border-gray-700/50 pb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">{{ $category->name }}</h1>
            <p class="text-gray-400">{{ $category->description }}</p>
        </div>
        @auth
            <a href="{{ route('forum.create') }}?category={{ $category->id }}" class="bg-brand hover:bg-teal-400 text-brand-dark font-bold py-2.5 px-6 rounded-full transition-colors shadow-lg shadow-brand/20 flex items-center gap-2 whitespace-nowrap">
                <i class='bx bx-plus text-lg'></i> New Thread
            </a>
        @endauth
    </div>

    <div class="bg-brand-card border border-gray-700/50 rounded-xl overflow-hidden shadow-lg">
        <div class="grid grid-cols-12 gap-4 p-4 bg-gray-900/80 text-xs text-gray-400 uppercase tracking-wider font-semibold border-b border-gray-700/50">
            <div class="col-span-8 md:col-span-6 pl-2">Thread</div>
            <div class="hidden md:block col-span-3 text-center">Author</div>
            <div class="col-span-4 md:col-span-3 text-right pr-4">Replies / Views</div>
        </div>

        @forelse($threads as $thread)
            <div class="grid grid-cols-12 gap-4 p-4 items-center border-b border-gray-700/50 last:border-0 hover:bg-white/5 transition-colors group">
                <div class="col-span-8 md:col-span-6 pl-2">
                    <a href="{{ route('forum.thread', $thread->slug) }}" class="text-lg font-semibold text-white group-hover:text-brand transition-colors block mb-1">
                        {{ $thread->title }}
                    </a>
                    <div class="text-xs text-gray-500 md:hidden mb-1">by <a href="{{ route('profile.show', $thread->user->username) }}" class="text-gray-400 hover:text-white">{{ $thread->user->username }}</a></div>
                    <div class="text-xs text-gray-500 flex items-center gap-1">
                        <i class='bx bx-time-five'></i> {{ $thread->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="hidden md:flex col-span-3 items-center justify-center gap-3">
                    <a href="{{ route('profile.show', $thread->user->username) }}" class="flex items-center gap-3 group/user">
                        @if($thread->user->avatar)
                            <img src="{{ asset('storage/' . $thread->user->avatar) }}" class="w-8 h-8 rounded-full border border-gray-600 object-cover group-hover/user:border-brand transition-colors">
                        @else
                            <div class="w-8 h-8 rounded-full bg-gray-800 flex items-center justify-center text-xs font-bold text-brand border border-gray-600 group-hover/user:border-brand transition-colors">{{ substr($thread->user->username, 0, 1) }}</div>
                        @endif
                        <span class="text-sm text-gray-300 group-hover/user:text-white transition-colors">{{ $thread->user->username }}</span>
                    </a>
                </div>
                <div class="col-span-4 md:col-span-3 text-right pr-4">
                    <div class="text-sm text-white font-medium flex justify-end items-center gap-2"><i class='bx bx-comment-detail text-gray-500'></i> {{ $thread->posts_count }}</div>
                    <div class="text-xs text-gray-500 flex justify-end items-center gap-1 mt-1"><i class='bx bx-show text-gray-600'></i> {{ $thread->views }}</div>
                </div>
            </div>
        @empty
            <div class="p-16 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-800 mb-4">
                    <i class='bx bx-message-square-dots text-2xl text-gray-400'></i>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No threads yet</h3>
                <p class="text-gray-400 mb-6 max-w-sm mx-auto">This category is empty. Be the first to start a discussion and get the conversation rolling!</p>
                @auth
                    <a href="{{ route('forum.create') }}?category={{ $category->id }}" class="inline-block bg-brand hover:bg-teal-400 text-brand-dark font-bold py-2 px-6 rounded-full transition-colors">
                        Start a Thread
                    </a>
                @endauth
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $threads->links() }}
    </div>
</div>
</x-indexLay>
