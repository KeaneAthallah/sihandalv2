<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sihandal' }} - Sistem Informasi Keuangan Daerah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface text-slate-800">
    <div
        x-data="{ sidebarCollapsed: false, mobileOpen: false }"
        x-on:keydown.escape.window="mobileOpen = false"
        class="min-h-screen"
    >
        {{-- Mobile overlay backdrop --}}
        <div
            x-show="mobileOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
            style="display: none;"
        ></div>

        @include('layouts.sidebar')

        <div class="flex flex-col min-h-screen transition-[margin] duration-300 ease-in-out lg:ml-[260px]" :class="sidebarCollapsed ? 'lg:ml-[76px]' : 'lg:ml-[260px]'">
            @include('layouts.navbar')
            <main class="flex-1 w-full max-w-[1600px] mx-auto p-4 lg:p-6">
                @if(isset($header))
                    {{ $header }}
                @endif
                {{ $slot }}
            </main>
            <footer class="px-4 lg:px-6 pb-4 text-center text-xs text-slate-400">
                © {{ date('Y') }} Sihandal — Sistem Informasi Keuangan Daerah
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
