@props([
    'actionLabel' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200/80 px-4 py-3.5 shadow-sm mb-6']) }}>
    <div class="flex items-center gap-3 flex-wrap">
        {{ $slot }}
        @if($actionLabel)
            <a href="{{ $actionUrl ?? '#' }}" class="ml-auto inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                <x-heroicon-o-plus class="w-4 h-4"/>
                {{ $actionLabel }}
            </a>
        @endif
    </div>
</div>
