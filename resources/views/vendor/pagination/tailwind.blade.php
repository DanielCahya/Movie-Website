@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center my-10 select-none">
        <div class="inline-flex items-center gap-1 sm:gap-2">
            {{-- First Page Link "<<" --}}
            @if (!$paginator->onFirstPage())
                <a wire:navigate.hover href="{{ $paginator->url(1) }}" rel="first" aria-label="First Page" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-brand hover:bg-slate-800/60 rounded-lg transition-colors" title="First Page">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Previous Page Link "<" --}}
            @if (!$paginator->onFirstPage())
                <a wire:navigate.hover href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous Page" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-brand hover:bg-slate-800/60 rounded-lg transition-colors" title="Previous Page">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
            @endif

            {{-- Pagination Elements (Array of links and string "...") --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true" class="w-8 h-9 flex items-center justify-center text-slate-500 font-bold select-none">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-800 border border-slate-700/80 text-brand font-bold text-sm shadow-md shadow-black/20">
                                {{ $page }}
                            </span>
                        @else
                            <a wire:navigate.hover href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-400 hover:text-brand hover:bg-slate-800/50 font-medium text-sm transition-colors">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link ">" --}}
            @if ($paginator->hasMorePages())
                <a wire:navigate.hover href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next Page" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-brand hover:bg-slate-800/60 rounded-lg transition-colors" title="Next Page">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @endif

            {{-- Last Page Link ">>" --}}
            @if ($paginator->hasMorePages() && method_exists($paginator, 'lastPage'))
                <a wire:navigate.hover href="{{ $paginator->url($paginator->lastPage()) }}" rel="last" aria-label="Last Page" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-brand hover:bg-slate-800/60 rounded-lg transition-colors" title="Last Page">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </a>
            @endif
        </div>
    </nav>
@endif

