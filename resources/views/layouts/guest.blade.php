<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sihandal') }} - Masuk</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .guest-bg {
            background: linear-gradient(160deg, #0F4C81 0%, #0a3560 40%, #071f3a 100%);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">

        {{-- Left: Branding Panel --}}
        <div class="hidden lg:flex lg:w-[45%] guest-bg relative overflow-hidden">
            {{-- Subtle geometric decoration --}}
            <div class="absolute inset-0 pointer-events-none">
                <svg class="absolute top-0 left-0 w-96 h-96 opacity-[0.04]" viewBox="0 0 400 400" fill="none">
                    <circle cx="200" cy="200" r="200" stroke="white" stroke-width="1" />
                    <circle cx="200" cy="200" r="150" stroke="white" stroke-width="1" />
                    <circle cx="200" cy="200" r="100" stroke="white" stroke-width="1" />
                </svg>
                <svg class="absolute bottom-0 right-0 w-80 h-80 opacity-[0.04]" viewBox="0 0 400 400" fill="none">
                    <rect x="50" y="50" width="300" height="300" rx="16" stroke="white" stroke-width="1" />
                    <rect x="100" y="100" width="200" height="200" rx="12" stroke="white" stroke-width="1" />
                </svg>
            </div>

            <div class="relative z-10 flex flex-col justify-center px-16 py-12 w-full">
                <div class="flex items-center gap-3 mb-10">
                    <img src="{{ asset('logo.png') }}" alt="Sihandal"
                        class="w-14 h-14 rounded-xl object-contain bg-white/10 p-2.5 border border-white/10" />
                    <span class="text-sm font-semibold text-white/80 tracking-wide uppercase">Sihandal</span>
                </div>

                <h1 class="text-3xl font-bold text-white leading-tight mb-3">
                    Sistem Informasi<br />Keuangan Daerah
                </h1>
                <p class="text-white/50 text-sm leading-relaxed max-w-sm">
                    Platform terpadu untuk pengelolaan anggaran, permintaan dana, dan monitoring realisasi keuangan daerah secara transparan dan akuntabel.
                </p>

                <div class="mt-12 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center shrink-0">
                            <x-heroicon-o-check class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-white/60 text-sm">Real-time monitoring keuangan daerah</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center shrink-0">
                            <x-heroicon-o-check class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-white/60 text-sm">Proses persetujuan multi-level</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center shrink-0">
                            <x-heroicon-o-check class="w-4 h-4 text-emerald-400" />
                        </div>
                        <span class="text-white/60 text-sm">Laporan analitik lengkap</span>
                    </div>
                </div>

                <div class="mt-auto pt-12">
                    <span class="text-xs text-white/30">&copy; {{ date('Y') }} Sihandal</span>
                </div>
            </div>
        </div>

        {{-- Right: Form Panel --}}
        <div class="w-full lg:w-[55%] flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md">

                {{-- Mobile: Logo above form --}}
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-12 h-12 rounded-xl object-contain" />
                    <span class="text-lg font-bold text-[var(--color-text)] tracking-tight">SIHANDAL</span>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
