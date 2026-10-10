@props(['priority' => 'normal'])

@php
    $config = match ($priority) {
        'high' => ['label' => 'Tinggi', 'classes' => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400'],
        'normal' => ['label' => 'Normal', 'classes' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'],
        'low' => ['label' => 'Rendah', 'classes' => 'bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300'],
        default => ['label' => $priority, 'classes' => 'bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $config['classes']]) }}>
    <span class="badge-dot"></span>
    {{ $config['label'] }}
</span>
