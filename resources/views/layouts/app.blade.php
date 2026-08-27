<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sihandal' }} - Sistem Informasi Keuangan Daerah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface text-slate-800">
    {{-- Global toast notifications --}}
    <div
        x-data
        x-on:toast.window="$store.toast.show($event.detail.message, $event.detail.type)"
        class="pointer-events-none fixed inset-x-0 top-0 z-[9999] flex flex-col items-center gap-3 p-4 sm:p-6"
        aria-live="polite"
    >
        <template x-for="toast in $store.toast.toasts" :key="toast.id">
            <div
                x-data="{ show: true }"
                x-init="$nextTick(() => { show = true })"
                x-show="show"
                x-transition:enter="animate-toast-enter"
                x-transition:leave="animate-toast-exit"
                :class="{
                    'border-emerald-200 bg-emerald-50 text-emerald-800': toast.type === 'success',
                    'border-red-200 bg-red-50 text-red-800': toast.type === 'error',
                    'border-amber-200 bg-amber-50 text-amber-800': toast.type === 'warning',
                    'border-slate-200 bg-white text-slate-700': toast.type === 'info'
                }"
                class="pointer-events-auto w-full max-w-sm rounded-xl border px-4 py-3 shadow-lg backdrop-blur-sm"
            >
                <div class="flex items-center gap-3">
                    <template x-if="toast.type === 'success'">
                        <svg class="h-5 w-5 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="h-5 w-5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="h-5 w-5 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    </template>
                    <p class="text-sm font-medium" x-text="toast.message"></p>
                </div>
            </div>
        </template>
    </div>

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

        <div class="flex flex-col min-h-screen transition-[margin] duration-300 ease-in-out lg:ml-[248px]" :class="sidebarCollapsed ? 'lg:ml-[64px]' : 'lg:ml-[248px]'">
            @include('layouts.navbar')
            <main class="flex-1 w-full mx-auto p-4 lg:p-6 print:p-0 print:bg-white">
                <div class="mx-auto w-full max-w-[1600px]">
                    @if(isset($header))
                        {{ $header }}
                    @endif
                    {{ $slot }}
                </div>
            </main>
            <footer class="border-t border-slate-200/80 px-4 lg:px-6 py-4 text-center text-xs text-slate-400 print:hidden">
                © {{ date('Y') }} Sihandal — Sistem Informasi Keuangan Daerah
            </footer>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
