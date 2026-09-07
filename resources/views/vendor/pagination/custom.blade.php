@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-4">
        {{-- Mobile: prev / next --}}
        <div class="flex items-center justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 rounded-lg cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Sebelumnya
                </a>
            @endif

            <span class="text-sm text-slate-500 tabular-nums">Hal {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition">
                    Berikutnya
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-slate-400 bg-white border border-slate-200 rounded-lg cursor-not-allowed">
                    Berikutnya
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>

        {{-- Desktop --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between gap-4 flex-1">
            <p class="text-xs text-slate-400 tabular-nums">
                Menampilkan
                <span class="font-medium text-slate-600">{{ $paginator->firstItem() }}</span>–
                <span class="font-medium text-slate-600">{{ $paginator->lastItem() }}</span>
                dari <span class="font-medium text-slate-600">{{ $paginator->total() }}</span> data
            </p>

            <span class="relative z-0 inline-flex items-center gap-1.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Sebelumnya">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 bg-white border border-slate-200 cursor-not-allowed" aria-hidden="true">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- Three Dots Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-sm font-medium text-slate-400">{{ $element }}</span>
                        </span>
                    @endif

                    {{-- Array of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-semibold text-white bg-primary border border-primary shadow-sm shadow-primary/20 tabular-nums">{{ $page }}</span>
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-medium text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition tabular-nums">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 bg-white border border-slate-200 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Berikutnya">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-400 bg-white border border-slate-200 cursor-not-allowed" aria-hidden="true">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif