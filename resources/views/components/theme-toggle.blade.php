@php
    $options = [
        'light' => ['label' => 'Terang', 'icon' => 'sun'],
        'dark' => ['label' => 'Gelap', 'icon' => 'moon'],
        'system' => ['label' => 'Sistem', 'icon' => 'computer-desktop'],
    ];
@endphp

<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <button
        type="button"
        @click="open = !open"
        class="relative p-2 text-content-muted hover:text-content hover:bg-surface-alt rounded-lg transition"
        aria-label="Ubah tema"
        x-bind:class="open && 'bg-surface-alt text-content'"
    >
        <template x-if="$store.theme.mode === 'light'">
            <x-heroicon-o-sun class="w-5 h-5"/>
        </template>
        <template x-if="$store.theme.mode === 'dark'">
            <x-heroicon-o-moon class="w-5 h-5"/>
        </template>
        <template x-if="$store.theme.mode === 'system'">
            <x-heroicon-o-computer-desktop class="w-5 h-5"/>
        </template>
    </button>

    <div
        x-show="open"
        x-cloak
        @click.away="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 mt-2 w-44 bg-card rounded-xl shadow-xl shadow-slate-200/50 dark:shadow-black/40 border border-border py-1.5 z-50"
    >
        @foreach ($options as $value => $option)
            <button
                type="button"
                @click="$store.theme.set('{{ $value }}'); open = false"
                class="flex items-center gap-3 w-full px-3.5 py-2 text-sm rounded-lg transition"
                x-bind:class="$store.theme.mode === '{{ $value }}'
                    ? 'text-primary font-medium'
                    : 'text-content-secondary hover:bg-surface-alt hover:text-content'"
            >
                <x-dynamic-component :component="'heroicon-o-' . $option['icon']" class="w-4 h-4 shrink-0"/>
                <span>{{ $option['label'] }}</span>
                <template x-if="$store.theme.mode === '{{ $value }}'">
                    <x-heroicon-s-check class="w-4 h-4 ml-auto shrink-0"/>
                </template>
            </button>
        @endforeach
    </div>
</div>
