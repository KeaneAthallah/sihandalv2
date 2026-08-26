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
                <nav class="flex items-center gap-1.5 mt-1.5 text-sm" aria-label="Breadcrumb">
                    @foreach($breadcrumbs as $index => $crumb)
                        @if($loop->first)
                            <span class="text-slate-400 font-medium">{{ $crumb }}</span>
                        @else
                            <x-heroicon-o-chevron-right class="w-3.5 h-3.5 text-slate-300 shrink-0"/>
                            @if($loop->last)
                                <span class="text-slate-700 font-medium truncate">{{ $crumb }}</span>
                            @else
                                <span class="text-slate-500 truncate">{{ $crumb }}</span>
                            @endif
                        @endif
                    @endforeach
                </nav>
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
