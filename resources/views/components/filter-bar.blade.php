@props([
    'actionLabel' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-slate-200 px-4 py-3 shadow-sm mb-5']) }}>
    <div class="flex items-center gap-3 flex-wrap">
        {{ $slot }}
        @if($actionLabel)
            <a href="{{ $actionUrl ?? '#' }}" class="ml-auto inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                <x-heroicon-o-plus class="w-4 h-4"/>
                {{ $actionLabel }}
            </a>
        @endif
    </div>
</div>
