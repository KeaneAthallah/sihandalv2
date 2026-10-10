<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <title>{{ config('app.name', 'Sihandal') }} - Masuk</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">
    @include('layouts.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-surface text-content min-h-screen">
    <div class="min-h-screen flex">

        {{-- Left: Branding Panel --}}
        <div class="hidden lg:flex lg:w-[45%] bg-primary relative overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,_rgba(255,255,255,0.08),_transparent_55%)]"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-white/[0.04] blur-2xl"></div>
            <div class="absolute -top-16 -left-16 w-72 h-72 rounded-full bg-white/[0.03] blur-2xl"></div>
            <div class="relative z-10 flex flex-col justify-center px-16 py-12 w-full">
                <div class="flex items-center mb-10">
                    <div class="inline-flex items-center max-w-full rounded-xl bg-white px-4 py-3 shadow-sm">
                        <img src="{{ asset('logo.png') }}" alt="Sihandal" class="h-80 max-w-full w-auto" />
                    </div>
                </div>

                <h1 class="text-3xl font-bold text-white leading-tight mb-3">
                    Sistem Informasi<br />Keuangan Daerah
                </h1>
                <p class="text-white/50 text-sm leading-relaxed max-w-sm">
                    Platform terpadu untuk pengelolaan anggaran, permintaan dana, dan monitoring realisasi keuangan daerah.
                </p>

                <div class="mt-12 space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-400/15 ring-1 ring-emerald-400/30 shrink-0">
                            <x-heroicon-o-check class="w-3.5 h-3.5 text-emerald-400" />
                        </span>
                        <span class="text-white/60 text-sm">Real-time monitoring keuangan daerah</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-400/15 ring-1 ring-emerald-400/30 shrink-0">
                            <x-heroicon-o-check class="w-3.5 h-3.5 text-emerald-400" />
                        </span>
                        <span class="text-white/60 text-sm">Proses persetujuan multi-level</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-400/15 ring-1 ring-emerald-400/30 shrink-0">
                            <x-heroicon-o-check class="w-3.5 h-3.5 text-emerald-400" />
                        </span>
                        <span class="text-white/60 text-sm">Laporan analitik lengkap</span>
                    </div>
                </div>

                <div class="mt-auto pt-12">
                    <span class="text-xs text-white/30">&copy; {{ date('Y') }} Sihandal</span>
                </div>
            </div>
        </div>

        {{-- Right: Form Panel --}}
        <div class="flex-1 flex items-center justify-center p-6 sm:p-8 bg-surface">
            <div class="w-full max-w-md">
                <div class="lg:hidden flex items-center gap-3 mb-8 justify-center">
                    <img src="{{ asset('logo-mark.png') }}" alt="Sihandal" class="h-10 w-auto" />
                    <span class="text-lg font-bold text-content tracking-tight">SIHANDAL</span>
                </div>
                <div class="card p-6 sm:p-8 shadow-xl shadow-slate-200/50 dark:shadow-black/40">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
