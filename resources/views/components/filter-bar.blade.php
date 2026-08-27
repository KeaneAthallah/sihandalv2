@props([
    'actionLabel' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'card px-4 py-3 mb-5']) }}>
    <div class="flex items-center gap-3 flex-wrap">
        {{ $slot }}
        @if($actionLabel)
            <a href="{{ $actionUrl ?? '#' }}" class="btn-primary ml-auto">
                <x-heroicon-o-plus class="w-4 h-4"/>
                {{ $actionLabel }}
            </a>
        @endif
    </div>
</div>
