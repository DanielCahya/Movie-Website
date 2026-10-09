<x-indexLay>
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="mb-6 flex flex-wrap items-center text-sm text-gray-400 gap-2">
        <a wire:navigate.hover href="{{ route('forum.index') }}" class="hover:text-brand transition-colors"><i class='bx bx-home'></i> Forums</a>
        <i class='bx bx-chevron-right text-gray-600'></i>
        <a wire:navigate.hover href="{{ route('forum.category', $thread->category->slug) }}" class="hover:text-brand transition-colors">{{ $thread->category->name }}</a>
        <i class='bx bx-chevron-right text-gray-600'></i>
        <span class="text-white opacity-80 truncate max-w-[200px] sm:max-w-xs">{{ $thread->title }}</span>
    </div>

    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4 border-l-4 border-brand pl-4">
        <h1 class="text-3xl font-bold text-white">{{ $thread->title }}</h1>
        <div class="flex items-center gap-4 text-sm text-gray-500 bg-gray-900/50 px-4 py-2 rounded-lg border border-gray-800">
            <span class="flex items-center gap-1"><i class='bx bx-show text-lg'></i> {{ $thread->views }} views</span>
            <span class="flex items-center gap-1"><i class='bx bx-comment-detail text-lg'></i> {{ $thread->posts->count() }} replies</span>
        </div>
    </div>

    <!-- Original Post -->
    <div class="bg-brand-card border border-brand/30 rounded-xl overflow-hidden shadow-lg mb-6 shadow-brand/5 relative">
        <div class="absolute top-0 right-0 bg-brand text-brand-dark text-[10px] font-bold px-3 py-1 rounded-bl-lg tracking-wider uppercase">Original Post</div>
        <div class="flex flex-col md:flex-row">
            <!-- User Info Sidebar -->
            <div class="w-full md:w-56 bg-gray-900/60 p-6 flex flex-col items-center border-b md:border-b-0 md:border-r border-gray-700/50">
                <a wire:navigate.hover href="{{ route('profile.show', $thread->user->username) }}" class="relative group block mb-4">
                    @if($thread->user->avatar)
                        <img src="{{ asset('storage/' . $thread->user->avatar) }}" class="w-24 h-24 rounded-full border-4 border-gray-800 group-hover:border-brand object-cover transition-colors shadow-xl">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gray-800 flex items-center justify-center text-3xl font-bold text-brand border-4 border-gray-700 group-hover:border-brand transition-colors shadow-xl">{{ substr($thread->user->username, 0, 1) }}</div>
                    @endif
                    <!-- Online indicator dot (fake for design) -->
                    <div class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 border-2 border-brand-card rounded-full"></div>
                </a>
                <a wire:navigate.hover href="{{ route('profile.show', $thread->user->username) }}" class="text-white font-bold text-lg hover:text-brand transition-colors text-center w-full truncate">{{ $thread->user->username }}</a>
                <div class="text-xs text-brand bg-brand/10 px-3 py-1 rounded-full mt-2 font-medium border border-brand/20">Topic Starter</div>
                <div class="mt-4 w-full grid grid-cols-2 gap-2 text-center text-xs text-gray-500 border-t border-gray-800 pt-4">
                    <div>
                        <div class="text-white font-medium">{{ $thread->user->threads->count() ?? 1 }}</div>
                        Threads
                    </div>
                    <div>
                        <div class="text-white font-medium">{{ $thread->user->posts->count() ?? 0 }}</div>
                        Posts
                    </div>
                </div>
            </div>
            <!-- Content Area -->
            <div class="flex-1 p-6 md:p-8 flex flex-col">
                <div class="flex justify-between items-center text-xs text-gray-500 mb-6 border-b border-gray-700/50 pb-4">
                    <span class="flex items-center gap-1 text-gray-400">
                        <i class='bx bx-calendar'></i> {{ $thread->created_at->format('M d, Y') }} at {{ $thread->created_at->format('h:i A') }}
                    </span>
                    <a href="#reply-form" class="text-gray-400 hover:text-brand transition-colors flex items-center gap-1">
                        <i class='bx bx-reply'></i> Quote
                    </a>
                </div>
                <div class="prose prose-invert max-w-none prose-p:text-gray-300 prose-headings:text-white prose-a:text-brand hover:prose-a:text-brand/80 flex-1 leading-relaxed text-base">
                    {!! nl2br(e($thread->content)) !!}
                </div>
            </div>
        </div>
    </div>

    <!-- Replies Section -->
    @if($thread->posts->count() > 0)
        <div class="flex items-center gap-4 mb-4 mt-10">
            <h3 class="text-xl font-bold text-white">Replies</h3>
            <div class="flex-1 h-px bg-gradient-to-r from-gray-700 to-transparent"></div>
        </div>

        @foreach($thread->posts as $post)
            <div class="bg-brand-card border border-gray-700/50 rounded-xl overflow-hidden shadow-lg mb-4">
                <div class="flex flex-col md:flex-row">
                    <!-- User Info Sidebar -->
                    <div class="w-full md:w-56 bg-gray-900/30 p-6 flex flex-col items-center border-b md:border-b-0 md:border-r border-gray-700/50">
                        <a wire:navigate.hover href="{{ route('profile.show', $post->user->username) }}" class="group block mb-3">
                            @if($post->user->avatar)
                                <img src="{{ asset('storage/' . $post->user->avatar) }}" class="w-16 h-16 rounded-full border-2 border-gray-700 group-hover:border-brand object-cover transition-colors">
                            @else
                                <div class="w-16 h-16 rounded-full bg-gray-800 flex items-center justify-center text-xl font-bold text-brand border-2 border-gray-700 group-hover:border-brand transition-colors">{{ substr($post->user->username, 0, 1) }}</div>
                            @endif
                        </a>
                        <a wire:navigate.hover href="{{ route('profile.show', $post->user->username) }}" class="text-gray-200 font-semibold hover:text-brand transition-colors text-center text-sm w-full truncate">{{ $post->user->username }}</a>
                        @if($post->user->id === $thread->user_id)
                            <div class="text-[10px] text-brand bg-brand/10 px-2 py-0.5 rounded mt-1 font-medium border border-brand/20">Topic Starter</div>
                        @endif
                    </div>
                    <!-- Content Area -->
                    <div class="flex-1 p-6 flex flex-col">
                        <div class="flex justify-between items-center text-xs text-gray-500 mb-4 border-b border-gray-700/50 pb-3">
                            <span class="flex items-center gap-1 text-gray-400">
                                <i class='bx bx-time'></i> {{ $post->created_at->diffForHumans() }}
                            </span>
                            <span class="text-gray-600">#{{ $loop->iteration }}</span>
                        </div>
                        <div class="prose prose-invert max-w-none text-gray-300 flex-1 leading-relaxed text-sm md:text-base">
                            {!! nl2br(e($post->content)) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <!-- Reply Form -->
    <div id="reply-form" class="mt-10 bg-brand-card border border-brand/20 rounded-xl p-6 shadow-xl relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute -right-10 -bottom-10 text-brand/5">
            <i class='bx bxs-chat text-9xl'></i>
        </div>

        <h3 class="text-xl font-bold text-white mb-6 relative z-10 flex items-center gap-2">
            <i class='bx bx-message-rounded-edit text-brand'></i> Write a Reply
        </h3>
        
        @auth
            <form action="{{ route('forum.reply', $thread->id) }}" method="POST" class="relative z-10">
                @csrf
                <div class="mb-4">
                    <textarea name="content" rows="5" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-4 text-white focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors resize-y shadow-inner" placeholder="Share your thoughts..." required></textarea>
                </div>
                <div class="flex justify-between items-center">
                    <div class="text-xs text-gray-500 flex items-center gap-1">
                        <i class='bx bx-info-circle'></i> Please be respectful and follow community guidelines.
                    </div>
                    <button type="submit" class="bg-brand hover:bg-teal-400 text-brand-dark font-bold py-2.5 px-8 rounded-lg transition-colors shadow-lg shadow-brand/20 flex items-center gap-2">
                        Post Reply <i class='bx bx-send'></i>
                    </button>
                </div>
            </form>
        @else
            <div class="bg-gray-900/60 rounded-xl p-8 text-center border border-gray-700/50 backdrop-blur-sm relative z-10">
                <i class='bx bx-lock-alt text-4xl text-gray-500 mb-3'></i>
                <p class="text-gray-300 mb-5 font-medium">You must be logged in to participate in this discussion.</p>
                <a wire:navigate.hover href="{{ route('login') }}" class="inline-block bg-brand hover:bg-teal-400 text-brand-dark font-bold py-2 px-8 rounded-full transition-colors shadow-lg">Login to Reply</a>
            </div>
        @endauth
    </div>
</div>
</x-indexLay>
