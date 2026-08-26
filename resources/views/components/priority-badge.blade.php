@props(['priority' => 'normal'])

@php
    $config = match($priority) {
        'high' => ['label' => 'Tinggi', 'classes' => 'bg-red-50 text-red-700 border-red-200'],
        'normal' => ['label' => 'Normal', 'classes' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'low' => ['label' => 'Rendah', 'classes' => 'bg-slate-100 text-slate-600 border-slate-200'],
        default => ['label' => $priority, 'classes' => 'bg-slate-100 text-slate-600 border-slate-200'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border whitespace-nowrap ' . $config['classes']]) }}>
    {{ $config['label'] }}
</span>
