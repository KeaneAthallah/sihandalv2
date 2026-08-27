@props([
    'title' => 'Sisa Kas Tersedia',
    'amount' => 'Rp 0',
    'percentage' => 0,
    'color' => 'success',
])

@php
    $barColor = match($color) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-primary',
        default => 'bg-emerald-500',
    };
    $bgColor = match($color) {
        'success' => 'bg-emerald-50',
        'warning' => 'bg-amber-50',
        'danger' => 'bg-red-50',
        'info' => 'bg-primary/5',
        default => 'bg-emerald-50',
    };
@endphp

<div {{ $attributes->merge(['class' => 'card p-4']) }}>
    <div class="flex items-center justify-between mb-2 gap-2">
        <span class="text-sm font-medium text-slate-600">{{ $title }}</span>
        <span class="text-xs font-semibold text-slate-500 tabular-nums">{{ $percentage }}%</span>
    </div>
    <p class="text-lg font-bold text-slate-800 mb-3 tabular-nums">{{ $amount }}</p>
    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
        <div class="{{ $barColor }} h-2 rounded-full transition-all duration-500" style="width: {{ max(0, min($percentage, 100)) }}%"></div>
    </div>
</div>
