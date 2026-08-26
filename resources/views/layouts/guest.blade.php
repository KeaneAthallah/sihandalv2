<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sihandal') }} - Masuk</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">

        {{-- Left: Branding Panel --}}
        <div class="hidden lg:flex lg:w-[45%] bg-primary relative overflow-hidden">
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
                    Platform terpadu untuk pengelolaan anggaran, permintaan dana, dan monitoring realisasi keuangan daerah.
                </p>

                <div class="mt-12 space-y-4">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-check class="w-4 h-4 text-emerald-400 shrink-0" />
                        <span class="text-white/60 text-sm">Real-time monitoring keuangan daerah</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-check class="w-4 h-4 text-emerald-400 shrink-0" />
                        <span class="text-white/60 text-sm">Proses persetujuan multi-level</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-check class="w-4 h-4 text-emerald-400 shrink-0" />
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
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-12 h-12 rounded-lg object-contain" />
                    <span class="text-lg font-bold text-slate-800 tracking-tight">SIHANDAL</span>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
