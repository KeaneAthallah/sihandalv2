@props([
    'title',
    'breadcrumbs' => [],
    'action' => null,
    'actionLabel' => null,
    'actionIcon' => null,
])

<div class="mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl font-bold text-slate-800">{{ $title }}</h1>
            @if(count($breadcrumbs) > 0)
                <nav class="flex items-center gap-1.5 mt-1 text-xs text-slate-400" aria-label="Breadcrumb">
                    @foreach($breadcrumbs as $index => $crumb)
                        @if(!$loop->first)
                            <x-heroicon-o-chevron-right class="w-3 h-3"/>
                        @endif
                        @if($loop->last)
                            <span class="text-slate-600 font-medium">{{ $crumb }}</span>
                        @else
                            <span>{{ $crumb }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif
        </div>
        @if($action)
            <a href="{{ $action }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
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
