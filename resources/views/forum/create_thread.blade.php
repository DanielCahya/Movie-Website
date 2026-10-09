<x-indexLay>
<div class="container mx-auto px-4 py-12 max-w-3xl">
    <div class="mb-8 flex items-center text-sm text-gray-400 gap-2">
        <a wire:navigate.hover href="{{ route('forum.index') }}" class="hover:text-brand transition-colors"><i class='bx bx-home'></i> Forums</a>
        <i class='bx bx-chevron-right text-gray-600'></i>
        <span class="text-white">Create New Thread</span>
    </div>

    <div class="bg-brand-card border border-gray-700/50 rounded-2xl p-6 md:p-10 shadow-2xl shadow-black/50 relative overflow-hidden">
        <!-- Decorative blob -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-brand/5 blur-3xl pointer-events-none"></div>

        <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-xl bg-brand/10 flex items-center justify-center border border-brand/20">
                <i class='bx bx-edit text-2xl text-brand'></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">Start a Discussion</h1>
                <p class="text-gray-400 text-sm mt-1">Share your thoughts, ask questions, or review a movie.</p>
            </div>
        </div>

        <form action="{{ route('forum.store') }}" method="POST" class="space-y-6 relative z-10">
            @csrf
            
            <div class="space-y-1.5">
                <label for="title" class="block text-sm font-semibold text-gray-300">Thread Title <span class="text-brand">*</span></label>
                <input type="text" id="title" name="title" class="w-full bg-gray-900/80 border border-gray-700 rounded-xl p-4 text-white text-lg focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors placeholder-gray-600 shadow-inner" placeholder="What's on your mind?" required autofocus>
            </div>

            <div class="space-y-1.5">
                <label for="category_id" class="block text-sm font-semibold text-gray-300">Category <span class="text-brand">*</span></label>
                <div class="relative">
                    <select id="category_id" name="category_id" class="w-full bg-gray-900/80 border border-gray-700 rounded-xl p-4 text-white appearance-none focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand transition-colors shadow-inner" required>
                        <option value="" disabled selected class="text-gray-500">Select a category for your thread...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-400">
                        <i class='bx bx-chevron-down text-xl'></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1 pl-1">Choose the category that best fits your topic.</p>
            </div>

            <div class="space-y-1.5">
                <label for="content" class="block text-sm font-semibold text-gray-300">Content <span class="text-brand">*</span></label>
                <div class="bg-gray-900/80 border border-gray-700 rounded-xl overflow-hidden focus-within:border-brand focus-within:ring-1 focus-within:ring-brand transition-colors shadow-inner">
                    <!-- Fake toolbar for aesthetic -->
                    <div class="flex items-center gap-2 bg-gray-800/80 border-b border-gray-700 p-2 text-gray-400">
                        <button type="button" class="p-1.5 hover:text-white hover:bg-gray-700 rounded transition-colors"><i class='bx bx-bold'></i></button>
                        <button type="button" class="p-1.5 hover:text-white hover:bg-gray-700 rounded transition-colors"><i class='bx bx-italic'></i></button>
                        <div class="w-px h-4 bg-gray-600 mx-1"></div>
                        <button type="button" class="p-1.5 hover:text-white hover:bg-gray-700 rounded transition-colors"><i class='bx bx-link'></i></button>
                        <button type="button" class="p-1.5 hover:text-white hover:bg-gray-700 rounded transition-colors"><i class='bx bx-image'></i></button>
                    </div>
                    <textarea id="content" name="content" rows="10" class="w-full bg-transparent p-4 text-white focus:outline-none resize-y placeholder-gray-600" placeholder="Write your post here. Be descriptive and detailed..." required></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-700/50 mt-8">
                <a wire:navigate.hover href="{{ url()->previous() !== url()->current() ? url()->previous() : route('forum.index') }}" class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-brand hover:bg-teal-400 text-brand-dark font-bold py-3 px-8 rounded-xl transition-all duration-300 shadow-lg shadow-brand/20 hover:shadow-brand/40 flex items-center gap-2 transform hover:-translate-y-0.5">
                    Post Thread <i class='bx bx-send'></i>
                </button>
            </div>
        </form>
    </div>
</div>
</x-indexLay>
