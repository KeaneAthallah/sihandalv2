<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sihandal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #0F4C81 0%, #0a3560 50%, #071f3a 100%);
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-12px);
            }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-delay {
            animation-delay: 0.2s;
            opacity: 0;
        }

        .fade-in-delay-2 {
            animation-delay: 0.4s;
            opacity: 0;
        }
    </style>
</head>

<body class="bg-white font-sans antialiased">
    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-200/60">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-20 h-20 rounded-xl object-contain" />
                <span class="text-lg font-bold text-slate-800">Sihandal</span>
            </div>
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                            Masuk
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="hero-gradient min-h-screen flex items-center pt-16">
        <div class="max-w-7xl mx-auto px-6 py-20 w-full">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                {{-- Left: Text --}}
                <div class="fade-in">
                    <img src="{{ asset('logo.png') }}" alt="Sihandal"
                        class="w-lg h-32 rounded-3xl object-contain bg-white p-8 mb-10  border border-white/20 shadow-lg" />
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 rounded-full text-white/80 text-sm font-medium mb-6 backdrop-blur-sm border border-white/10">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                        Sistem Informasi Keuangan Daerah
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-bold text-white leading-tight mb-6">
                        Kelola Keuangan Daerah
                        <span class="text-blue-300">Lebih Mudah</span>
                    </h1>
                    <p class="text-lg text-white/70 mb-8 max-w-lg leading-relaxed">
                        Platform terpadu untuk pengelolaan anggaran, permintaan dana, dan monitoring realisasi keuangan
                        daerah secara transparan dan akuntabel.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}"
                                class="px-8 py-3.5 bg-white text-primary text-sm font-bold rounded-xl hover:bg-slate-50 transition-all shadow-lg">
                                Mulai Sekarang
                            </a>
                        @endif
                        <a href="#fitur"
                            class="px-8 py-3.5 bg-white/10 text-white text-sm font-semibold rounded-xl hover:bg-white/20 transition-all border border-white/20 backdrop-blur-sm">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                </div>

                {{-- Right: Cards --}}
                <div class="hidden lg:block fade-in fade-in-delay">
                    <div class="relative">
                        {{-- Main Card --}}
                        <div
                            class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 border border-white/20 float-animation">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-emerald-500/20 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-emerald-400" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-white/60 text-sm">Total Anggaran</p>
                                    <p class="text-white text-2xl font-bold">Rp 89.2 M</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="bg-white/10 rounded-xl p-4 flex items-center justify-between">
                                    <span class="text-white/70 text-sm">Penerimaan</span>
                                    <span class="text-emerald-400 font-semibold">Rp 45.8 M</span>
                                </div>
                                <div class="bg-white/10 rounded-xl p-4 flex items-center justify-between">
                                    <span class="text-white/70 text-sm">Pengeluaran</span>
                                    <span class="text-red-400 font-semibold">Rp 28.3 M</span>
                                </div>
                                <div class="bg-white/10 rounded-xl p-4 flex items-center justify-between">
                                    <span class="text-white/70 text-sm">Sisa Kas</span>
                                    <span class="text-blue-300 font-semibold">Rp 17.5 M</span>
                                </div>
                            </div>
                        </div>

                        {{-- Floating Badge --}}
                        <div class="absolute -top-4 -right-4 bg-white rounded-2xl p-4 shadow-xl">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Pending</p>
                                    <p class="text-sm font-bold text-slate-800">5 Permintaan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="fitur" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-slate-800 mb-4">Fitur Unggulan</h2>
                <p class="text-slate-500 max-w-2xl mx-auto">Solusi lengkap untuk pengelolaan keuangan daerah yang
                    transparan dan efisien</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                    $features = [
                        [
                            'icon' => 'banknotes',
                            'title' => 'Sumber Dana',
                            'desc' =>
                                'Kelola dan monitoring berbagai sumber dana seperti DAK, DAU, DBH, PAD, dan lainnya.',
                        ],
                        [
                            'icon' => 'building-office-2',
                            'title' => 'Manajemen OPD',
                            'desc' => 'Koordinasi keuangan antar Organisasi Perangkat Daerah secara terpusat.',
                        ],
                        [
                            'icon' => 'document-text',
                            'title' => 'Permintaan Dana',
                            'desc' => 'Proses permintaan dana dari OPD hingga realisasi dengan timeline yang jelas.',
                        ],
                        [
                            'icon' => 'check-circle',
                            'title' => 'Persetujuan',
                            'desc' => 'Alur persetujuan multi-level yang transparan dan terdokumentasi.',
                        ],
                        [
                            'icon' => 'chart-bar',
                            'title' => 'Laporan Realtime',
                            'desc' => 'Dashboard analitik dengan visualisasi data keuangan real-time.',
                        ],
                        [
                            'icon' => 'arrow-right-on-rectangle',
                            'title' => 'Transfer Dana',
                            'desc' => 'Proses transfer dana yang aman dan terintegrasi dengan sistem perbankan.',
                        ],
                    ];
                @endphp

                @foreach ($features as $feature)
                    <div
                        class="bg-white rounded-2xl p-8 border border-slate-200 hover:shadow-lg hover:border-slate-300 transition-all group">
                        <div
                            class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center mb-5 group-hover:bg-primary/20 transition-all">
                            @switch($feature['icon'])
                                @case('banknotes')
                                    <x-heroicon-o-banknotes class="w-6 h-6 text-primary" />
                                @break

                                @case('building-office-2')
                                    <x-heroicon-o-building-office-2 class="w-6 h-6 text-primary" />
                                @break

                                @case('document-text')
                                    <x-heroicon-o-document-text class="w-6 h-6 text-primary" />
                                @break

                                @case('check-circle')
                                    <x-heroicon-o-check-circle class="w-6 h-6 text-primary" />
                                @break

                                @case('chart-bar')
                                    <x-heroicon-o-chart-bar class="w-6 h-6 text-primary" />
                                @break

                                @case('arrow-right-on-rectangle')
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-6 h-6 text-primary" />
                                @break
                            @endswitch
                        </div>
                        <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200 py-8">
        <div class="max-w-7xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-16 h-16 rounded-xl object-contain" />
                <span class="text-sm text-slate-500">&copy; {{ date('Y') }} Sihandal. Sistem Informasi Keuangan
                    Daerah.</span>
            </div>
            <span class="text-xs text-slate-400">v{{ app()->version() }}</span>
        </div>
    </footer>
</body>

</html>
