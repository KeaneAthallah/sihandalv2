@props([
    'title',
    'value',
    'change' => null,
    'changeType' => 'up',
    'color' => 'primary',
])

@php
    $colorClasses = match($color) {
        'primary' => 'bg-primary/10 text-primary',
        'success' => 'bg-emerald-50 text-emerald-600',
        'danger' => 'bg-red-50 text-red-600',
        'warning' => 'bg-amber-50 text-amber-600',
        'info' => 'bg-purple-50 text-purple-600',
        default => 'bg-primary/10 text-primary',
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200 p-5 lg:p-6 shadow-sm hover:shadow-md transition-all duration-200']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-xs lg:text-sm font-medium text-slate-500">{{ $title }}</p>
            <p class="mt-2 text-xl lg:text-2xl font-bold text-slate-800 truncate">{{ $value }}</p>
            @if($change)
                <div class="mt-2 flex items-center gap-1.5">
                    @if($changeType === 'up')
                        <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-emerald-500"/>
                    @else
                        <x-heroicon-o-arrow-trending-down class="w-4 h-4 text-red-500"/>
                    @endif
                    <span class="text-xs lg:text-sm font-medium {{ $changeType === 'up' ? 'text-emerald-600' : 'text-red-600' }} truncate">{{ $change }}</span>
                </div>
            @endif
        </div>
        @if(isset($icon))
            <div class="p-2.5 lg:p-3 rounded-xl {{ $colorClasses }} shrink-0">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
