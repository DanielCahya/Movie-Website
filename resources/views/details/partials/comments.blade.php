<div class="mt-8 pt-8 border-t border-white/5">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-lg font-medium text-gray-300">
            {{ $comments->total() }} comments
        </h3>
        <div class="flex items-center bg-brand-card rounded-md p-1">
            <button class="px-4 py-1.5 text-xs font-semibold text-white bg-white/10 rounded shadow-sm">Best</button>
            <button class="px-4 py-1.5 text-xs font-semibold text-gray-400 hover:text-white transition-colors">Newest</button>
            <button class="px-4 py-1.5 text-xs font-semibold text-gray-400 hover:text-white transition-colors">Oldest</button>
        </div>
    </div>

    <!-- Add Comment Form -->
    @auth
    <form action="{{ route('media.comment.store') }}" method="POST" class="mb-12">
        @csrf
        <input type="hidden" name="media_id" value="{{ $media['id'] }}">
        <input type="hidden" name="media_type" value="{{ $type }}">
        <input type="hidden" name="media_title" value="{{ $media['name'] ?? $media['title'] ?? 'Unknown' }}">
        <div class="flex gap-4 items-start">
            <div class="w-10 h-10 rounded-full flex-none overflow-hidden bg-brand-card">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-sm font-bold text-white uppercase bg-blue-900">
                        {{ substr(Auth::user()->username, 0, 1) }}
                    </div>
                @endif
            </div>
            <div class="flex-grow">
                <textarea name="content" rows="1" class="w-full bg-white/5 border border-white/5 rounded-md p-4 text-gray-300 placeholder-gray-500 focus:ring-1 focus:ring-brand focus:border-brand resize-none text-sm transition-colors" placeholder="Share your thoughts" required></textarea>
                <div class="mt-2 flex justify-end">
                    <button type="submit" class="bg-brand hover:bg-cyan-400 text-gray-900 font-semibold py-1.5 px-6 rounded transition-colors shadow-lg">Post</button>
                </div>
            </div>
        </div>
    </form>
    @else
    <div class="mb-12 flex items-start gap-4">
        <div class="w-10 h-10 rounded-full flex-none bg-brand-card flex items-center justify-center text-gray-600"><i class='bx bx-user'></i></div>
        <div class="flex-grow bg-white/5 border border-white/5 rounded-md p-4 text-gray-500 text-sm flex items-center justify-between">
            <span>Share your thoughts</span>
            <a href="{{ route('login') }}" class="bg-brand hover:bg-cyan-400 text-gray-900 font-semibold py-1.5 px-6 rounded transition-colors shadow-lg">Log in</a>
        </div>
    </div>
    @endauth

    <!-- Comments List -->
    <div class="space-y-8">
        @forelse($comments as $comment)
            <div class="flex gap-4 group" x-data="{ editMode: false, openReply: false }">
                <a href="{{ route('profile.show', $comment->user->username) }}" class="w-10 h-10 rounded-full flex-none overflow-hidden bg-brand-card mt-1 block">
                    @if($comment->user->avatar)
                        <img src="{{ asset('storage/' . $comment->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-sm font-bold text-white uppercase bg-blue-900">
                            {{ substr($comment->user->username, 0, 1) }}
                        </div>
                    @endif
                </a>
                <div class="flex-grow">
                    <div class="mb-1 flex items-center">
                        <a href="{{ route('profile.show', $comment->user->username) }}" class="font-bold text-gray-200 text-sm hover:text-white transition-colors mr-2">{{ $comment->user->username }}</a>
                        @if($comment->user->role === 'admin' || $comment->user->role === 'superadmin')
                            <span class="text-brand text-[10px] font-bold mr-2">MOD</span>
                        @endif
                        <span class="text-xs text-gray-600">{{ str_replace(' ago', ' ago', $comment->created_at->diffForHumans()) }}</span>
                    </div>
                    
                    <div class="mt-2">
                        <p x-show="!editMode" class="text-gray-300 text-sm whitespace-pre-wrap leading-relaxed">{{ $comment->content }}</p>
                        
                        <!-- Edit Form -->
                        <form x-show="editMode" style="display: none;" action="{{ route('media.comment.update', $comment->id) }}" method="POST" class="mb-3 mt-2">
                            @csrf @method('PUT')
                            <textarea name="content" rows="2" class="w-full bg-brand-bg border-none rounded p-3 text-gray-300 focus:ring-0 resize-none text-sm mb-2" required>{{ $comment->content }}</textarea>
                            <div class="flex gap-2 justify-end">
                                <button type="button" @click="editMode = false" class="text-gray-500 hover:text-white text-xs font-semibold px-3 py-1">Cancel</button>
                                <button type="submit" class="bg-brand hover:bg-cyan-400 text-white font-semibold py-1 px-4 text-xs rounded transition-colors">Save</button>
                            </div>
                        </form>
                        
                        <!-- Actions -->
                        <div class="flex items-center gap-5 mt-3 text-gray-500 text-xs font-semibold">
                            <!-- Like -->
                            @auth
                            @php
                                $userReaction = $comment->likes->where('user_id', Auth::id())->first();
                                $hasLiked = $userReaction && !$userReaction->is_dislike;
                                $hasDisliked = $userReaction && $userReaction->is_dislike;
                                $likesCount = $comment->likes->where('is_dislike', false)->count();
                                $dislikesCount = $comment->likes->where('is_dislike', true)->count();
                            @endphp
                            <div class="flex items-center gap-5" x-data="{ 
                                userReaction: '{{ $hasLiked ? 'like' : ($hasDisliked ? 'dislike' : '') }}',
                                likesCount: {{ $likesCount }},
                                dislikesCount: {{ $dislikesCount }},
                                toggleReaction(type) {
                                    fetch('{{ route('media.comment.react', $comment->id) }}', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                        body: JSON.stringify({ is_dislike: type === 'dislike' })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        this.userReaction = data.user_reaction;
                                        this.likesCount = data.likes_count;
                                        this.dislikesCount = data.dislikes_count;
                                    });
                                }
                            }">
                                <!-- Like -->
                                <button @click.prevent="toggleReaction('like')" :class="userReaction === 'like' ? 'text-white' : 'hover:text-gray-300'" class="flex items-center gap-1.5 transition-colors">
                                    <i class='bx' :class="userReaction === 'like' ? 'bxs-like' : 'bx-like'"></i> <span x-text="likesCount"></span>
                                </button>
                                <!-- Dislike -->
                                <button @click.prevent="toggleReaction('dislike')" :class="userReaction === 'dislike' ? 'text-white' : 'hover:text-gray-300'" class="flex items-center gap-1.5 transition-colors">
                                    <i class='bx' :class="userReaction === 'dislike' ? 'bxs-dislike' : 'bx-dislike'"></i> <span x-text="dislikesCount"></span>
                                </button>
                            </div>

                            <!-- Reply Toggle -->
                            <div>
                                <button @click="openReply = !openReply" class="hover:text-gray-300 transition-colors flex items-center gap-1.5">
                                    <i class='bx bx-share bx-flip-horizontal'></i> Reply
                                </button>
                                
                                <!-- Inline Reply Form -->
                            </div>
                            

                            <div x-data="{ openMenu: false }" class="relative ml-2">
                                <button @click="openMenu = !openMenu" @click.outside="openMenu = false" class="hover:text-gray-300 transition-colors flex items-center gap-1">
                                    <i class='bx bx-dots-horizontal-rounded'></i> More
                                </button>
                                
                                <div x-show="openMenu" style="display: none;" class="absolute top-full mt-1 left-0 bg-brand-bg border border-white/5 rounded-md shadow-xl py-1 z-20 w-32">
                                    @if(Auth::id() === $comment->user_id)
                                        <button @click="editMode = true; openMenu = false" class="w-full text-left px-4 py-2 hover:bg-white/5 text-gray-300 text-xs font-semibold transition-colors">Edit</button>
                                    @endif
                                    @if(Auth::id() === $comment->user_id || in_array(Auth::user()->role, ['admin', 'superadmin']))
                                        <form action="{{ route('media.comment.destroy', $comment->id) }}" method="POST" onsubmit="return confirm('Delete this comment?');" class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-white/5 text-red-500 text-xs font-semibold transition-colors">Delete</button>
                                        </form>
                                    @endif
                                    @if(Auth::id() !== $comment->user_id)
                                        <button class="w-full text-left px-4 py-2 hover:bg-white/5 text-gray-300 text-xs font-semibold transition-colors" onclick="alert('Block user coming soon!')">Block User</button>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="flex items-center gap-5">
                                <a href="{{ route('login') }}" class="flex items-center gap-1.5 hover:text-gray-300 transition-colors">
                                    <i class='bx bx-like'></i> {{ $comment->likes->where('is_dislike', false)->count() }}
                                </a>
                                <a href="{{ route('login') }}" class="flex items-center gap-1.5 hover:text-gray-300 transition-colors">
                                    <i class='bx bx-dislike'></i> {{ $comment->likes->where('is_dislike', true)->count() }}
                                </a>
                            </div>
                            @endauth
                        </div>
                    </div>
                    
                    <!-- Inline Reply Form (Top Level) -->
                    <div x-show="openReply" style="display: none;" class="mt-4 pl-4 border-l border-white/5">
                        @auth
                        <form action="{{ route('media.comment.store') }}" method="POST" class="flex gap-3 items-start">
                            @csrf
                            <input type="hidden" name="media_id" value="{{ $media['id'] }}">
                            <input type="hidden" name="media_type" value="{{ $type }}">
                            <input type="hidden" name="media_title" value="{{ $media['name'] ?? $media['title'] ?? 'Unknown' }}">
                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                            <div class="w-8 h-8 rounded-full flex-none overflow-hidden bg-brand-card hidden sm:block">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-xs font-bold text-white uppercase bg-brand">
                                        {{ substr(Auth::user()->username, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow">
                                <textarea name="content" rows="2" class="w-full bg-white/5 border border-white/5 rounded-md p-3 text-gray-300 placeholder-gray-500 focus:ring-1 focus:ring-brand focus:border-brand resize-none text-sm mb-2" placeholder="Reply to {{ $comment->user->username }}..." required></textarea>
                                <div class="flex gap-3 justify-end items-center">
                                    <button type="button" @click="openReply = false" class="text-gray-400 hover:text-white text-sm font-semibold transition-colors">Cancel</button>
                                    <button type="submit" class="bg-brand hover:bg-cyan-400 text-gray-900 font-bold py-1.5 px-6 rounded transition-colors shadow-lg">Reply</button>
                                </div>
                            </div>
                        </form>
                        @else
                        <div class="bg-white/5 border border-white/5 rounded-md p-3 text-gray-500 text-sm flex items-center justify-between">
                            <span>Log in to reply</span>
                            <a href="{{ route('login') }}" class="bg-brand hover:bg-cyan-400 text-gray-900 font-semibold py-1 px-4 rounded transition-colors shadow-lg">Log in</a>
                        </div>
                        @endauth
                    </div>
                    
                    <!-- Nested Replies -->
                    @if($comment->replies->count() > 0)
                        <div class="mt-5 space-y-5 pl-5 border-l border-white/5/50" x-data="{ visibleReplies: 5 }">
                            @foreach($comment->replies as $reply)
                                <div class="flex gap-4 group" x-show="{{ $loop->index }} < visibleReplies" @if($loop->index >= 5) style="display: none;" @endif x-data="{ replyEditMode: false, openReply: false }">
                                    <a href="{{ route('profile.show', $reply->user->username) }}" class="w-8 h-8 rounded-full flex-none overflow-hidden bg-brand-card block">
                                        @if($reply->user->avatar)
                                            <img src="{{ asset('storage/' . $reply->user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-xs font-bold text-white uppercase bg-blue-800">
                                                {{ substr($reply->user->username, 0, 1) }}
                                            </div>
                                        @endif
                                    </a>
                                    <div class="flex-grow">
                                        <div class="mb-1 flex items-center">
                                            <a href="{{ route('profile.show', $reply->user->username) }}" class="font-bold text-gray-200 text-sm hover:text-white transition-colors mr-2">{{ $reply->user->username }}</a>
                                            @if($reply->user->role === 'admin' || $reply->user->role === 'superadmin')
                                                <span class="text-brand text-[10px] font-bold mr-2">MOD</span>
                                            @endif
                                            <span class="text-xs text-gray-600">{{ str_replace(' ago', ' ago', $reply->created_at->diffForHumans()) }}</span>
                                        </div>
                                        
                                        <p x-show="!replyEditMode" class="text-gray-300 text-sm whitespace-pre-wrap leading-relaxed">{{ $reply->content }}</p>
                                        
                                        <!-- Edit Form for Reply -->
                                        <form x-show="replyEditMode" style="display: none;" action="{{ route('media.comment.update', $reply->id) }}" method="POST" class="mb-2 mt-2">
                                            @csrf @method('PUT')
                                            <textarea name="content" rows="2" class="w-full bg-brand-bg border-none rounded p-3 text-gray-300 focus:ring-0 resize-none text-sm mb-2" required>{{ $reply->content }}</textarea>
                                            <div class="flex gap-2 justify-end">
                                                <button type="button" @click="replyEditMode = false" class="text-gray-500 hover:text-white text-xs font-semibold px-3 py-1">Cancel</button>
                                                <button type="submit" class="bg-brand hover:bg-cyan-400 text-white font-semibold py-1 px-4 text-xs rounded transition-colors">Save</button>
                                            </div>
                                        </form>
                                        
                                        <!-- Reply Actions -->
                                        @auth
                                        <div class="flex items-center gap-5 mt-3 text-gray-500 text-xs font-semibold">
                                            @php
                                                $userReactionReply = $reply->likes->where('user_id', Auth::id())->first();
                                                $hasLikedReply = $userReactionReply && !$userReactionReply->is_dislike;
                                                $hasDislikedReply = $userReactionReply && $userReactionReply->is_dislike;
                                                $likesCountReply = $reply->likes->where('is_dislike', false)->count();
                                                $dislikesCountReply = $reply->likes->where('is_dislike', true)->count();
                                            @endphp
                                            <div class="flex items-center gap-5" x-data="{ 
                                                userReaction: '{{ $hasLikedReply ? 'like' : ($hasDislikedReply ? 'dislike' : '') }}',
                                                likesCount: {{ $likesCountReply }},
                                                dislikesCount: {{ $dislikesCountReply }},
                                                toggleReaction(type) {
                                                    fetch('{{ route('media.comment.react', $reply->id) }}', {
                                                        method: 'POST',
                                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                                        body: JSON.stringify({ is_dislike: type === 'dislike' })
                                                    })
                                                    .then(res => res.json())
                                                    .then(data => {
                                                        this.userReaction = data.user_reaction;
                                                        this.likesCount = data.likes_count;
                                                        this.dislikesCount = data.dislikes_count;
                                                    });
                                                }
                                            }">
                                                <!-- Like -->
                                                <button @click.prevent="toggleReaction('like')" :class="userReaction === 'like' ? 'text-white' : 'hover:text-gray-300'" class="flex items-center gap-1.5 transition-colors">
                                                    <i class='bx' :class="userReaction === 'like' ? 'bxs-like' : 'bx-like'"></i> <span x-text="likesCount"></span>
                                                </button>
                                                <!-- Dislike -->
                                                <button @click.prevent="toggleReaction('dislike')" :class="userReaction === 'dislike' ? 'text-white' : 'hover:text-gray-300'" class="flex items-center gap-1.5 transition-colors">
                                                    <i class='bx' :class="userReaction === 'dislike' ? 'bxs-dislike' : 'bx-dislike'"></i> <span x-text="dislikesCount"></span>
                                                </button>
                                            </div>

                                            <div>
                                                <button @click="openReply = !openReply" class="hover:text-gray-300 transition-colors flex items-center gap-1.5">
                                                    <i class='bx bx-share bx-flip-horizontal'></i> Reply
                                                </button>
                                            </div>

                                            <div x-data="{ openMenu: false }" class="relative ml-2">
                                                <button @click="openMenu = !openMenu" @click.outside="openMenu = false" class="hover:text-gray-300 transition-colors flex items-center gap-1">
                                                    <i class='bx bx-dots-horizontal-rounded'></i> More
                                                </button>
                                                
                                                <div x-show="openMenu" style="display: none;" class="absolute top-full mt-1 left-0 bg-brand-bg border border-white/5 rounded-md shadow-xl py-1 z-20 w-32">
                                                    @if(Auth::id() === $reply->user_id)
                                                        <button @click="replyEditMode = true; openMenu = false" class="w-full text-left px-4 py-2 hover:bg-white/5 text-gray-300 text-xs font-semibold transition-colors">Edit</button>
                                                    @endif
                                                    @if(Auth::id() === $reply->user_id || in_array(Auth::user()->role, ['admin', 'superadmin']))
                                                        <form action="{{ route('media.comment.destroy', $reply->id) }}" method="POST" onsubmit="return confirm('Delete this reply?');" class="m-0">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="w-full text-left px-4 py-2 hover:bg-white/5 text-red-500 text-xs font-semibold transition-colors">Delete</button>
                                                        </form>
                                                    @endif
                                                    @if(Auth::id() !== $reply->user_id)
                                                        <button class="w-full text-left px-4 py-2 hover:bg-white/5 text-gray-300 text-xs font-semibold transition-colors" onclick="alert('Block user coming soon!')">Block User</button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Inline Reply Form (Nested Level) -->
                                        <div x-show="openReply" style="display: none;" class="mt-4 pl-4 border-l border-white/5">
                                            <form action="{{ route('media.comment.store') }}" method="POST" class="flex gap-3 items-start">
                                                @csrf
                                                <input type="hidden" name="media_id" value="{{ $media['id'] }}">
                                                <input type="hidden" name="media_type" value="{{ $type }}">
                                                <input type="hidden" name="media_title" value="{{ $media['name'] ?? $media['title'] ?? 'Unknown' }}">
                                                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                                <div class="w-8 h-8 rounded-full flex-none overflow-hidden bg-brand-card hidden sm:block">
                                                    @if(Auth::user()->avatar)
                                                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center text-xs font-bold text-white uppercase bg-brand">
                                                            {{ substr(Auth::user()->username, 0, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-grow">
                                                    <textarea name="content" rows="2" class="w-full bg-white/5 border border-white/5 rounded-md p-3 text-gray-300 placeholder-gray-500 focus:ring-1 focus:ring-brand focus:border-brand resize-none text-sm mb-2" placeholder="Reply to {{ $reply->user->username }}..." required></textarea>
                                                    <div class="flex gap-3 justify-end items-center">
                                                        <button type="button" @click="openReply = false" class="text-gray-400 hover:text-white text-sm font-semibold transition-colors">Cancel</button>
                                                        <button type="submit" class="bg-brand hover:bg-cyan-400 text-gray-900 font-bold py-1.5 px-6 rounded transition-colors shadow-lg">Reply</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        @else
                                        <div class="flex items-center gap-5 mt-3 text-gray-500 text-xs font-semibold">
                                            <a href="{{ route('login') }}" class="flex items-center gap-1.5 hover:text-gray-300 transition-colors">
                                                <i class='bx bx-like'></i> {{ $reply->likes->where('is_dislike', false)->count() }}
                                            </a>
                                            <a href="{{ route('login') }}" class="flex items-center gap-1.5 hover:text-gray-300 transition-colors">
                                                <i class='bx bx-dislike'></i> {{ $reply->likes->where('is_dislike', true)->count() }}
                                            </a>
                                        </div>
                                        @endauth
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($comment->replies->count() > 5)
                                <button x-show="visibleReplies < {{ $comment->replies->count() }}" @click="visibleReplies += 5" class="text-xs text-gray-400 hover:text-white font-semibold transition-colors mt-2 flex items-center gap-1">
                                    <i class='bx bx-chevron-down'></i> Show more replies (<span x-text="{{ $comment->replies->count() }} - visibleReplies > 5 ? 5 : {{ $comment->replies->count() }} - visibleReplies"></span>)
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <i class='bx bx-message-square-detail text-4xl text-gray-700 mb-3'></i>
                <p class="text-gray-500 text-sm">No comments yet. Be the first to share your thoughts!</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    @if($comments->hasPages())
        <div class="mt-8 border-t border-white/5 pt-6">
            {{ $comments->links() }}
        </div>
    @endif
</div>
