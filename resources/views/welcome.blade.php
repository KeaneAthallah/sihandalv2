<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sihandal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-bg {
            background: linear-gradient(160deg, #0F4C81 0%, #0a3560 40%, #071f3a 100%);
        }

        .fade-up {
            animation: fadeUp 0.7s ease-out both;
        }

        .fade-up-d1 {
            animation-delay: 0.15s;
        }

        .fade-up-d2 {
            animation-delay: 0.3s;
        }

        .fade-up-d3 {
            animation-delay: 0.45s;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="bg-[var(--color-surface)] font-sans antialiased text-[var(--color-text)]">

    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-xl border-b border-[var(--color-border)]">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-10 h-10 rounded-lg object-contain" />
                <span class="text-base font-bold text-[var(--color-text)] tracking-tight">SIHANDAL</span>
            </div>
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="px-5 py-2 bg-[var(--color-primary)] text-white text-sm font-semibold rounded-lg hover:bg-[var(--color-primary-light)] transition-colors shadow-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-5 py-2 bg-[var(--color-primary)] text-white text-sm font-semibold rounded-lg hover:bg-[var(--color-primary-light)] transition-colors shadow-sm">
                            Masuk
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="hero-bg min-h-screen flex items-center pt-16">
        <div class="max-w-6xl mx-auto px-6 py-20 w-full">
            <div class="max-w-2xl fade-up">
                <div class="flex items-center gap-3 mb-8">
                    <img src="{{ asset('logo.png') }}" alt="Sihandal"
                        class="w-16 h-16 rounded-xl object-contain bg-white/10 p-2 border border-white/10" />
                    <span class="text-sm font-semibold text-white/80 tracking-wide uppercase">Sihandal</span>
                </div>
                <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight mb-4 fade-up fade-up-d1">
                    Sistem Informasi Keuangan Daerah
                </h1>
                <p class="text-lg text-white/60 mb-10 max-w-xl leading-relaxed fade-up fade-up-d2">
                    Platform terpadu untuk pengelolaan anggaran, permintaan dana, dan monitoring realisasi
                    keuangan daerah secara transparan dan akuntabel.
                </p>
                <div class="flex flex-wrap gap-4 fade-up fade-up-d3">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 px-7 py-3 bg-white text-[var(--color-primary)] text-sm font-bold rounded-lg hover:bg-white/90 transition-colors shadow-lg">
                            Masuk ke Sistem
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </a>
                    @endif
                    <a href="#fitur"
                        class="inline-flex items-center gap-2 px-7 py-3 bg-white/10 text-white text-sm font-semibold rounded-lg hover:bg-white/20 transition-colors border border-white/20">
                        Pelajari Fitur
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section id="fitur" class="py-24 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-[var(--color-text)] mb-3">Fitur Utama</h2>
                <p class="text-[var(--color-text-secondary)] max-w-lg mx-auto">
                    Solusi lengkap untuk pengelolaan keuangan daerah yang transparan dan efisien
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $features = [
                        [
                            'icon' => 'heroicon-o-banknotes',
                            'title' => 'Sumber Dana',
                            'desc' => 'Kelola dan monitor berbagai sumber dana seperti DAK, DAU, DBH, PAD, dan lainnya.',
                        ],
                        [
                            'icon' => 'heroicon-o-building-office-2',
                            'title' => 'Manajemen OPD',
                            'desc' => 'Koordinasi keuangan antar Organisasi Perangkat Daerah secara terpusat.',
                        ],
                        [
                            'icon' => 'heroicon-o-document-text',
                            'title' => 'Permintaan Dana',
                            'desc' => 'Proses permintaan dana dari OPD hingga realisasi dengan timeline yang jelas.',
                        ],
                        [
                            'icon' => 'heroicon-o-check-circle',
                            'title' => 'Persetujuan',
                            'desc' => 'Alur persetujuan multi-level yang transparan dan terdokumentasi.',
                        ],
                        [
                            'icon' => 'heroicon-o-chart-bar',
                            'title' => 'Laporan Realtime',
                            'desc' => 'Dashboard analitik dengan visualisasi data keuangan real-time.',
                        ],
                        [
                            'icon' => 'heroicon-o-arrow-right-on-rectangle',
                            'title' => 'Transfer Dana',
                            'desc' => 'Proses transfer dana yang aman dan terintegrasi dengan sistem perbankan.',
                        ],
                    ];
                @endphp

                @foreach ($features as $feature)
                    <div
                        class="bg-white rounded-xl p-7 border border-[var(--color-border)] hover:shadow-lg hover:border-[var(--color-primary-100)] transition-all group">
                        <div
                            class="w-11 h-11 bg-[var(--color-primary-50)] rounded-lg flex items-center justify-center mb-4 group-hover:bg-[var(--color-primary-100)] transition-colors">
                            <x-dynamic-component :component="$feature['icon']" class="w-5 h-5 text-[var(--color-primary)]" />
                        </div>
                        <h3 class="text-base font-semibold text-[var(--color-text)] mb-1.5">{{ $feature['title'] }}</h3>
                        <p class="text-sm text-[var(--color-text-secondary)] leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-white border-t border-[var(--color-border)] py-8">
        <div class="max-w-6xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Sihandal" class="w-8 h-8 rounded-lg object-contain" />
                <span class="text-sm text-[var(--color-text-secondary)]">
                    &copy; {{ date('Y') }} Sihandal &mdash; Sistem Informasi Keuangan Daerah
                </span>
            </div>
            <span class="text-xs text-[var(--color-text-muted)]">v{{ app()->version() }}</span>
        </div>
    </footer>
</body>

</html>
