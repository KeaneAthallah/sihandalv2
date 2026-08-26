@props(['priority' => 'normal'])

@php
    $config = match($priority) {
        'high' => ['label' => 'Tinggi', 'classes' => 'bg-red-50 text-red-700'],
        'normal' => ['label' => 'Normal', 'classes' => 'bg-blue-50 text-blue-700'],
        'low' => ['label' => 'Rendah', 'classes' => 'bg-slate-100 text-slate-600'],
        default => ['label' => $priority, 'classes' => 'bg-slate-100 text-slate-600'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap ' . $config['classes']]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
    {{ $config['label'] }}
</span>
