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
            <h1 class="page-heading">{{ $title }}</h1>
            @if(count($breadcrumbs) > 0)
                <nav class="flex items-center gap-1.5 mt-1.5 text-xs text-slate-400" aria-label="Breadcrumb">
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
        @if(isset($actions))
            <div class="flex items-center gap-2 flex-wrap">
                {{ $actions }}
            </div>
        @elseif($action)
            <a href="{{ $action }}" class="btn-primary">
                @if($actionIcon)
                    {!! $actionIcon !!}
                @endif
                {{ $actionLabel }}
            </a>
        @endif
    </div>
</div>
