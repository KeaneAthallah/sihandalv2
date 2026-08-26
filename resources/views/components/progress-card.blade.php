@props([
    'title' => 'Sisa Kas Tersedia',
    'amount' => 'Rp 12.000.000.000',
    'percentage' => 80,
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

<div {{ $attributes->merge(['class' => 'rounded-2xl p-5 border border-transparent', $bgColor]) }}>
    <div class="flex items-center justify-between mb-2 gap-2">
        <span class="text-sm font-medium text-slate-600 truncate">{{ $title }}</span>
        <span class="text-xs font-semibold text-slate-500 shrink-0">{{ $percentage }}%</span>
    </div>
    <p class="text-lg lg:text-xl font-bold text-slate-800 mb-3 truncate">{{ $amount }}</p>
    <div class="w-full bg-white/60 rounded-full h-2.5 overflow-hidden">
        <div class="{{ $barColor }} h-2.5 rounded-full transition-all duration-500" style="width: {{ max(0, min($percentage, 100)) }}%"></div>
    </div>
</div>
