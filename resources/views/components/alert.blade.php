@props([
    'type' => 'success',
    'title' => null,
    'dismissible' => false,
])

@php
    $config = match ($type) {
        'success' => ['wrapper' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'icon' => 'text-emerald-500', 'name' => 'check-circle'],
        'error', 'danger' => ['wrapper' => 'bg-red-50 border-red-200 text-red-700', 'icon' => 'text-red-500', 'name' => 'x-circle'],
        'warning' => ['wrapper' => 'bg-amber-50 border-amber-200 text-amber-700', 'icon' => 'text-amber-500', 'name' => 'exclamation-triangle'],
        'info' => ['wrapper' => 'bg-blue-50 border-blue-200 text-blue-700', 'icon' => 'text-blue-500', 'name' => 'information-circle'],
        default => ['wrapper' => 'bg-slate-50 border-slate-200 text-slate-700', 'icon' => 'text-slate-500', 'name' => 'information-circle'],
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
