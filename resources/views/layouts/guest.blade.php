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
        .hero-gradient {
            background: linear-gradient(135deg, #0F4C81 0%, #0a3560 50%, #071f3a 100%);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen flex">
        {{-- Left: Branding --}}
        <div class="hidden lg:flex lg:w-1/2 hero-gradient relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" fill="none">
                    <circle cx="20" cy="20" r="40" stroke="white" stroke-width="0.5"/>
                    <circle cx="80" cy="80" r="60" stroke="white" stroke-width="0.5"/>
                    <circle cx="50" cy="50" r="80" stroke="white" stroke-width="0.3"/>
                </svg>
            </div>
            <div class="relative z-10 flex flex-col justify-center px-16 w-full">
                <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-36 h-36 rounded-2xl object-contain bg-white/10 p-5 mb-8" />
                <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                    Sihandal
                </h1>
                <p class="text-xl text-blue-200 mb-2">Sistem Informasi Keuangan Daerah</p>
                <p class="text-white/60 leading-relaxed max-w-md">
                    Platform terpadu untuk pengelolaan anggaran, permintaan dana, dan monitoring realisasi keuangan daerah.
                </p>

                <div class="mt-12 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <span class="text-white/80 text-sm">Real-time monitoring keuangan daerah</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <span class="text-white/80 text-sm">Proses persetujuan multi-level</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <span class="text-white/80 text-sm">Laporan analitik lengkap</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md">
                {{-- Mobile Logo --}}
                <div class="lg:hidden flex items-center gap-3 mb-8">
                    <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-16 h-16 rounded-xl object-contain" />
                    <span class="text-xl font-bold text-slate-800">Sihandal</span>
                </div>

                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
