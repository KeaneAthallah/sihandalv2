<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <title>{{ config('app.name', 'Sihandal') }} - Sistem Informasi Keuangan Daerah</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700">
    @include('layouts.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-surface font-sans antialiased text-content">

    @php
        $compact = function (float $value): string {
            $abs = abs($value);
            if ($abs >= 1_000_000_000_000) {
                return 'Rp ' . number_format($value / 1_000_000_000_000, 1, ',', '.') . ' Triliun';
            }
            if ($abs >= 1_000_000_000) {
                return 'Rp ' . number_format($value / 1_000_000_000, 1, ',', '.') . ' Miliar';
            }
            if ($abs >= 1_000_000) {
                return 'Rp ' . number_format($value / 1_000_000, 0, ',', '.') . ' Juta';
            }
            return 'Rp ' . number_format($value, 0, ',', '.');
        };
    @endphp

    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 bg-card/90 backdrop-blur-lg border-b border-border">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="/" class="flex items-center gap-2.5">
                    <img src="{{ asset('logo-mark.png') }}" alt="Sihandal" class="h-9 w-auto">
                    <span class="font-bold text-xl text-content">Sihandal</span>
                </a>
                <div class="flex items-center gap-3">
                    <x-theme-toggle />
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary text-sm">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary text-sm">Masuk</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-linear-to-b from-primary-50/70 via-surface to-surface dark:from-primary-950/40 dark:via-surface dark:to-surface">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[600px] bg-linear-to-b from-primary-200/25 to-transparent dark:from-primary-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-20 md:pt-24 md:pb-28">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                {{-- Left: Text --}}
                <div class="text-center lg:text-left">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 mb-5">Platform Resmi Pemerintah Daerah</span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-content tracking-tight leading-tight mb-4">
                        Kelola Anggaran Daerah<br>
                        <span class="text-primary-600 dark:text-primary-400">Mudah, Cepat, Transparan</span>
                    </h1>
                    <p class="text-lg md:text-xl text-content-muted leading-relaxed max-w-xl mx-auto lg:mx-0 mb-8">
                        Sihandal adalah sistem informasi terpadu untuk pengelolaan keuangan daerah —
                        mencatat penerimaan, pengeluaran, dan memonitor realisasi anggaran secara real-time
                        oleh seluruh OPD.
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="btn-primary text-base px-8 py-3 shadow-lg shadow-primary/20">
                                Masuk ke Sistem
                            </a>
                        @endif
                        <a href="#tentang" class="btn-secondary text-base px-8 py-3">
                            Pelajari Fitur
                        </a>
                    </div>
                    {{-- Mini stats --}}
                    <div class="flex flex-wrap justify-center lg:justify-start gap-6 mt-10 pt-8 border-t border-border">
                        <div>
                            <p class="text-2xl font-bold text-content">{{ number_format($sumberDanaCount, 0, ',', '.') }}</p>
                            <p class="text-xs text-content-muted">Sumber Dana</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-content">{{ number_format($opdCount, 0, ',', '.') }}</p>
                            <p class="text-xs text-content-muted">OPD Aktif</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-content">24/7</p>
                            <p class="text-xs text-content-muted">Akses Online</p>
                        </div>
                    </div>
                </div>

                {{-- Right: Preview card --}}
                <div class="hidden lg:flex justify-center">
                    <div class="relative w-full max-w-md">
                        <div class="card shadow-xl p-8">
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-light">
                                <img src="{{ asset('logo-mark.png') }}" alt="" class="h-8 w-auto">
                                <span class="font-bold text-content">Sihandal</span>
                                <span class="ml-auto badge bg-blue-100 dark:bg-blue-500/15 text-blue-700 dark:text-blue-300">Live</span>
                            </div>
                            <div class="space-y-4">
                                <div class="bg-surface-alt rounded-xl p-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-medium text-content-secondary">Total Pagu</span>
                                        <span class="text-xs text-content-muted">TA {{ $tahunAnggaran ?? date('Y') }}</span>
                                    </div>
                                    <p class="text-lg font-bold text-content">{{ $compact($totalPagu) }}</p>
                                    <div class="mt-2 w-full bg-border rounded-full h-2">
                                        <div class="bg-primary-500 h-2 rounded-full" style="width: {{ min(100, round($persenRealisasi)) }}%"></div>
                                    </div>
                                    <p class="text-xs text-content-muted mt-1">{{ number_format($persenRealisasi, 0, ',', '.') }}% terealisasi</p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-emerald-50 dark:bg-emerald-500/10 rounded-xl p-3">
                                        <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Penerimaan</p>
                                        <p class="text-sm font-bold text-content">{{ $compact($totalPenerimaan) }}</p>
                                    </div>
                                    <div class="bg-red-50 dark:bg-red-500/10 rounded-xl p-3">
                                        <p class="text-xs text-red-600 dark:text-red-400 font-medium">Pengeluaran</p>
                                        <p class="text-sm font-bold text-content">{{ $compact($totalPengeluaran) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Decorative circles --}}
                        <div class="absolute -top-4 -right-4 w-24 h-24 bg-primary-200/40 dark:bg-primary-500/10 rounded-full -z-10"></div>
                        <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-primary-200/30 dark:bg-primary-500/10 rounded-full -z-10"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- About --}}
    <section id="tentang" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 mb-4">Tentang</span>
                <h2 class="text-3xl md:text-4xl font-bold text-content tracking-tight mb-4">Apa Itu <span class="text-primary-600 dark:text-primary-400">Sihandal</span>?</h2>
                <p class="text-content-muted leading-relaxed mb-4">
                    <strong class="text-content-secondary">Sistem Informasi Keuangan Daerah</strong> (Sihandal) adalah platform digital
                    yang dikembangkan untuk membantu Pemerintah Daerah dalam mengelola data keuangan secara terintegrasi.
                </p>
                <p class="text-content-muted leading-relaxed mb-6">
                    Mulai dari pencatatan sumber dana, realisasi anggaran, penerimaan, hingga pengeluaran —
                    semua tersaji dalam satu sistem yang aman, transparan, dan dapat diakses kapan saja.
                    Sihandal memudahkan setiap OPD untuk mengajukan transaksi dan memudahkan admin
                    untuk melakukan monitoring serta persetujuan.
                </p>
                <div class="flex flex-wrap gap-4">
                    @foreach (['Berbasis Web', 'Multi-OPD', 'Approval Workflow', 'Real-time Dashboard'] as $point)
                        <div class="flex items-center gap-2 text-sm text-content-secondary">
                            <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-500"/>
                            {{ $point }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                @foreach ([[number_format($sumberDanaCount, 0, ',', '.'), 'Sumber Dana'], [number_format($opdCount, 0, ',', '.'), 'OPD Terdaftar'], ['Real-time', 'Monitoring'], ['24/7', 'Akses Online']] as [$value, $label])
                    <div class="card p-6 text-center hover:border-primary/30 transition-colors">
                        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $value }}</p>
                        <p class="text-sm text-content-muted mt-1">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="bg-surface-alt/60 border-y border-border py-20 md:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 mb-4">Alur Kerja</span>
                <h2 class="text-3xl md:text-4xl font-bold text-content tracking-tight">Bagaimana Cara Kerjanya?</h2>
                <p class="text-content-muted mt-3 max-w-xl mx-auto">Empat langkah mudah dalam mengelola keuangan daerah.</p>
            </div>
            <div class="grid md:grid-cols-4 gap-8">
                @php
                    $steps = [
                        [1, 'Input Sumber Dana', 'Admin memasukkan data sumber dana dan pagu anggaran per OPD.'],
                        [2, 'Ajukan Transaksi', 'Setiap OPD mencatat penerimaan dan pengeluaran sesuai kegiatan.'],
                        [3, 'Approval', 'Admin melakukan verifikasi dan menyetujui atau menolak transaksi.'],
                        [4, 'Monitoring', 'Dashboard real-time menampilkan progres realisasi anggaran.'],
                    ];
                @endphp
                @foreach ($steps as [$num, $title, $desc])
                    <div class="text-center">
                        <div class="w-14 h-14 bg-primary-100 dark:bg-primary-500/15 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <span class="text-xl font-bold text-primary-600 dark:text-primary-400">{{ $num }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-content mb-2">{{ $title }}</h3>
                        <p class="text-sm text-content-muted leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
        <div class="text-center mb-14">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-500/15 text-primary-700 dark:text-primary-300 mb-4">Fitur</span>
            <h2 class="text-3xl md:text-4xl font-bold text-content tracking-tight">Fitur Unggulan</h2>
            <p class="text-content-muted mt-3 max-w-xl mx-auto">Semua fitur dirancang untuk memudahkan pengelolaan keuangan daerah.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @php
                $features = [
                    ['heroicon-o-document-text', 'primary', 'Manajemen Sumber Dana', 'Kelola data sumber dana, pagu anggaran, dan alokasi per OPD.'],
                    ['heroicon-o-arrow-down-left', 'emerald', 'Pencatatan Penerimaan', 'Catat setiap penerimaan dana dengan detail sumber, jumlah, dan tanggal.'],
                    ['heroicon-o-arrow-up-right', 'red', 'Pencatatan Pengeluaran', 'Kelola pengeluaran anggaran per kegiatan dan sub kegiatan.'],
                    ['heroicon-o-chart-bar', 'amber', 'Realisasi & Laporan', 'Pantau realisasi anggaran secara real-time per OPD dan kegiatan.'],
                    ['heroicon-o-presentation-chart-line', 'purple', 'Dashboard Interaktif', 'Visualisasi data keuangan dalam bentuk grafik dan angka ringkas.'],
                    ['heroicon-o-shield-check', 'sky', 'Keamanan & Role-based', 'Hak akses berbasis peran (admin & user) untuk setiap OPD.'],
                ];
                $chip = [
                    'primary' => 'bg-primary-100 dark:bg-primary-500/15 text-primary-600 dark:text-primary-400',
                    'emerald' => 'bg-emerald-100 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
                    'red' => 'bg-red-100 dark:bg-red-500/15 text-red-600 dark:text-red-400',
                    'amber' => 'bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400',
                    'purple' => 'bg-purple-100 dark:bg-purple-500/15 text-purple-600 dark:text-purple-400',
                    'sky' => 'bg-sky-100 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400',
                ];
            @endphp
            @foreach ($features as [$icon, $color, $title, $desc])
                <div class="card p-6 hover:border-primary/30 transition-colors">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5 {{ $chip[$color] }}">
                        <x-dynamic-component :component="$icon" class="w-6 h-6"/>
                    </div>
                    <h3 class="text-lg font-semibold text-content mb-2">{{ $title }}</h3>
                    <p class="text-content-muted text-sm leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-linear-to-br from-primary-600 via-primary-700 to-primary-900 py-16 md:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-3">Siap Mengelola Anggaran dengan Lebih Baik?</h2>
            <p class="text-primary-100/90 text-lg mb-8 max-w-2xl mx-auto">Bergabunglah dengan puluhan OPD yang sudah menggunakan Sihandal untuk transparansi dan efisiensi pengelolaan keuangan daerah.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-3 bg-white text-primary-700 font-semibold rounded-xl hover:bg-primary-50 transition-all duration-200 shadow-lg">
                        Masuk ke Sistem
                    </a>
                @endif
                <a href="#tentang" class="inline-flex items-center justify-center px-8 py-3 bg-white/10 text-white font-semibold rounded-xl border border-white/20 hover:bg-white/20 transition-all duration-200">
                    Pelajari Fitur
                </a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-border bg-card">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('logo-mark.png') }}" alt="Sihandal" class="h-8 w-auto">
                    <span class="font-semibold text-content-secondary">Sihandal</span>
                </div>
                <p class="text-sm text-content-muted">&copy; {{ date('Y') }} Sihandal. Sistem Informasi Keuangan Daerah.</p>
            </div>
        </div>
    </footer>
</body>

</html>
