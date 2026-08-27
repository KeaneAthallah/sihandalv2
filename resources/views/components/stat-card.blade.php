@props([
    'title',
    'value',
    'change' => null,
    'changeType' => 'up',
    'color' => 'primary',
])

@php
    $iconBg = match($color) {
        'primary' => 'bg-primary/10 text-primary',
        'success' => 'bg-emerald-50 text-emerald-600',
        'danger' => 'bg-red-50 text-red-600',
        'warning' => 'bg-amber-50 text-amber-600',
        'info' => 'bg-purple-50 text-purple-600',
        default => 'bg-primary/10 text-primary',
    };
@endphp

<div {{ $attributes->merge(['class' => 'card p-4']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="stat-label">{{ $title }}</p>
            <p class="stat-value truncate">{{ $value }}</p>
            @if($change)
                <div class="mt-1.5 flex items-center gap-1">
                    @if($changeType === 'up')
                        <x-heroicon-o-arrow-trending-up class="w-3.5 h-3.5 text-emerald-500"/>
                    @else
                        <x-heroicon-o-arrow-trending-down class="w-3.5 h-3.5 text-red-500"/>
                    @endif
                    <span class="text-xs font-medium {{ $changeType === 'up' ? 'text-emerald-600' : 'text-red-600' }}">{{ $change }}</span>
                </div>
            @endif
        </div>
        @if(isset($icon))
            <div class="p-2.5 rounded-xl {{ $iconBg }} shrink-0 ring-1 ring-inset ring-black/[0.03]">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
