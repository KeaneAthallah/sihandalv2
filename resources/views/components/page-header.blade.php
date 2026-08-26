@props([
    'title',
    'breadcrumbs' => [],
    'action' => null,
    'actionLabel' => null,
    'actionIcon' => null,
])

<div class="mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl lg:text-2xl font-bold text-slate-800 tracking-tight">{{ $title }}</h1>
            @if(count($breadcrumbs) > 0)
                <div class="flex items-center gap-2 mt-1 text-sm">
                    @foreach($breadcrumbs as $index => $crumb)
                        @if($loop->first)
                            <span class="text-slate-400">{{ $crumb }}</span>
                        @else
                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                            <span class="text-slate-600 font-medium truncate">{{ $crumb }}</span>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        @if($action)
            <a href="{{ $action }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                @if($actionIcon)
                    {!! $actionIcon !!}
                @endif
                {{ $actionLabel }}
            </a>
        @endif
        @if(isset($actions))
            <div class="flex items-center gap-2 flex-wrap">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
