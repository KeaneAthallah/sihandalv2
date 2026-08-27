@props(['priority' => 'normal'])

@php
    $config = match ($priority) {
        'high' => ['label' => 'Tinggi', 'classes' => 'bg-red-50 text-red-700'],
        'normal' => ['label' => 'Normal', 'classes' => 'bg-blue-50 text-blue-700'],
        'low' => ['label' => 'Rendah', 'classes' => 'bg-slate-100 text-slate-600'],
        default => ['label' => $priority, 'classes' => 'bg-slate-100 text-slate-600'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $config['classes']]) }}>
    <span class="badge-dot"></span>
    {{ $config['label'] }}
</span>
