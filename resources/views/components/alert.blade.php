@props([
    'type' => 'success',
    'title' => null,
    'dismissible' => false,
])

@php
    $config = match ($type) {
        'success' => ['wrapper' => 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300', 'icon' => 'text-emerald-500', 'name' => 'check-circle'],
        'error', 'danger' => ['wrapper' => 'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-300', 'icon' => 'text-red-500', 'name' => 'x-circle'],
        'warning' => ['wrapper' => 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-300', 'icon' => 'text-amber-500', 'name' => 'exclamation-triangle'],
        'info' => ['wrapper' => 'bg-blue-50 dark:bg-blue-500/10 border-blue-200 dark:border-blue-500/30 text-blue-700 dark:text-blue-300', 'icon' => 'text-blue-500', 'name' => 'information-circle'],
        default => ['wrapper' => 'bg-surface-alt border-border text-content-secondary', 'icon' => 'text-content-muted', 'name' => 'information-circle'],
    };
@endphp

<div x-data="{ show: true }" x-show="show"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     {{ $attributes->merge(['class' => 'mb-5 flex items-start gap-3 px-4 py-3 rounded-xl border text-sm shadow-sm ' . $config['wrapper']]) }}>
    <div class="shrink-0 mt-0.5">
        <x-dynamic-component :component="'heroicon-o-' . $config['name']" class="w-4 h-4 {{ $config['icon'] }}"/>
    </div>
    <div class="flex-1 min-w-0">
        @if($title)
            <p class="font-semibold">{{ $title }}</p>
        @endif
        <div class="leading-snug">{{ $slot }}</div>
    </div>
    @if($dismissible)
        <button @click="show = false" class="shrink-0 p-0.5 -mr-1 opacity-50 hover:opacity-100 transition rounded hover:bg-black/5" aria-label="Tutup">
            <x-heroicon-o-x-mark class="w-4 h-4"/>
        </button>
    @endif
</div>
