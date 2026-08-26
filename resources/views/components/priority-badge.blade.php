@props(['priority' => 'normal'])

@php
    $config = match($priority) {
        'high' => ['label' => 'Tinggi', 'classes' => 'bg-red-50 text-red-700 border-red-200/80 shadow-sm shadow-red-100'],
        'normal' => ['label' => 'Normal', 'classes' => 'bg-blue-50 text-blue-700 border-blue-200/80 shadow-sm shadow-blue-100'],
        'low' => ['label' => 'Rendah', 'classes' => 'bg-slate-100 text-slate-600 border-slate-200/80 shadow-sm shadow-slate-100'],
        default => ['label' => $priority, 'classes' => 'bg-slate-100 text-slate-600 border-slate-200/80 shadow-sm shadow-slate-100'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border whitespace-nowrap ' . $config['classes']]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
    {{ $config['label'] }}
</span>
