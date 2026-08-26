<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" :breadcrumbs="['Dashboard']">
            <x-slot name="actions">
                <span class="text-xs text-slate-400 font-medium">Tahun Anggaran {{ date('Y') }}</span>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Financial Summary Hero --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        @php
            $anggaranFormatted = number_format($totalAnggaran / 1000000000, 2, ',', '.');
            $penerimaanFormatted = number_format($totalPenerimaan / 1000000000, 2, ',', '.');
            $pengeluaranFormatted = number_format($totalPengeluaran / 1000000000, 2, ',', '.');
            $sisaFormatted = number_format($sisaKas / 1000000000, 2, ',', '.');
            $penerimaanPersen = $totalAnggaran > 0 ? round(($totalPenerimaan / $totalAnggaran) * 100, 1) : 0;
            $pengeluaranPersen = $totalAnggaran > 0 ? round(($totalPengeluaran / $totalAnggaran) * 100, 1) : 0;
        @endphp

        {{-- Total Anggaran --}}
        <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-5 lg:p-6 text-white shadow-lg shadow-slate-900/10">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-300">Total Anggaran</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold tracking-tight">Rp {{ $anggaranFormatted }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar Rupiah</p>
                </div>
                <div class="p-2.5 rounded-xl bg-white/10 shrink-0">
                    <x-heroicon-o-banknotes class="w-6 h-6 text-white"/>
                </div>
            </div>
        </div>

        {{-- Penerimaan --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Realisasi Penerimaan</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-emerald-600 tracking-tight">Rp {{ $penerimaanFormatted }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar &middot; {{ $penerimaanPersen }}% dari anggaran</p>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-50 shrink-0">
                    <x-heroicon-o-arrow-down-left class="w-6 h-6 text-emerald-500"/>
                </div>
            </div>
        </div>

        {{-- Pengeluaran --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Realisasi Pengeluaran</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-red-500 tracking-tight">Rp {{ $pengeluaranFormatted }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar &middot; {{ $pengeluaranPersen }}% dari anggaran</p>
                </div>
                <div class="p-2.5 rounded-xl bg-red-50 shrink-0">
                    <x-heroicon-o-arrow-up-right class="w-6 h-6 text-red-500"/>
                </div>
            </div>
        </div>

        {{-- Pending --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Permintaan Pending</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-amber-500 tracking-tight">{{ number_format($permintaanPending) }}</p>
                    <p class="text-xs text-slate-400 mt-1">permintaan menunggu verifikasi</p>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-50 shrink-0">
                    <x-heroicon-o-clock class="w-6 h-6 text-amber-500"/>
                </div>
            </div>
        </div>
    </div>

    {{-- Sisa Kas Progress --}}
    @php
        $sisaKasPersentase = $sisaKasMax > 0 ? min(round(($sisaKas / $sisaKasMax) * 100), 100) : 0;
        $sisaKasColor = $sisaKasPersentase >= 60 ? 'emerald' : ($sisaKasPersentase >= 30 ? 'amber' : 'red');
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200 p-5 lg:p-6 shadow-sm mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-xl bg-{{ $sisaKasColor }}-50">
                    <x-heroicon-o-wallet class="w-5 h-5 text-{{ $sisaKasColor }}-500"/>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Sisa Kas</h3>
                    <p class="text-xs text-slate-400">Saldo kas yang tersedia</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-lg lg:text-xl font-extrabold text-slate-800">Rp {{ number_format($sisaKas, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400">{{ $sisaKasPersentase }}% dari kapasitas</p>
            </div>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
            <div class="bg-{{ $sisaKasColor }}-500 h-3 rounded-full transition-all duration-700 ease-out" style="width: {{ max(0, min($sisaKasPersentase, 100)) }}%"></div>
        </div>
    </div>

    {{-- Charts Row 1: Bar + Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-5 mb-6">
        {{-- Main Chart --}}
        <div class="lg:col-span-2">
            <x-chart-card title="Top OPD Realisasi" subtitle="Realisasi pengeluaran per organisasi">
                <div id="bar-chart" class="w-full min-h-[380px]"></div>
            </x-chart-card>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="px-5 lg:px-6 py-4 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Aktivitas Terbaru</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Riwayat permintaan dana</p>
                    </div>
                    <span class="px-2.5 py-1 bg-slate-100 text-slate-500 text-xs font-semibold rounded-lg">{{ $recentPermintaan->count() }}</span>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto px-5 lg:px-6 py-4">
                <div class="space-y-1">
                    @forelse($recentPermintaan as $index => $item)
                        @php
                            $activity = match($item->status) {
                                'draft' => ['icon' => 'document-text', 'dot' => 'bg-slate-300', 'text' => 'Dibuat'],
                                'menunggu' => ['icon' => 'clock', 'dot' => 'bg-amber-400', 'text' => 'Diajukan'],
                                'disetujui' => ['icon' => 'check-circle', 'dot' => 'bg-emerald-400', 'text' => 'Disetujui'],
                                'ditolak' => ['icon' => 'x-circle', 'dot' => 'bg-red-400', 'text' => 'Ditolak'],
                                default => ['icon' => 'document-text', 'dot' => 'bg-slate-300', 'text' => $item->status],
                            };
                        @endphp
                        <div class="relative flex items-start gap-3 group py-2.5 {{ $index !== $recentPermintaan->count() - 1 ? 'border-b border-slate-50' : '' }}">
                            <div class="relative z-10 mt-0.5">
                                <span class="block w-2.5 h-2.5 rounded-full {{ $activity['dot'] }}"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-slate-700 truncate">{{ $item->nomor_permintaan }}</p>
                                    <span class="text-xs font-medium text-slate-400 shrink-0">{{ $activity['text'] }}</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $item->opd->nama ?? 'OPD' }} &middot; {{ $item->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <div class="p-3 rounded-full bg-slate-50 mb-3">
                                <x-heroicon-o-inbox class="w-8 h-8 text-slate-300"/>
                            </div>
                            <p class="text-sm text-slate-400">Belum ada aktivitas</p>
                            <p class="text-xs text-slate-300 mt-1">Aktivitas akan muncul di sini</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 2: Donut + Radial --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5 mb-6">
        {{-- Status Distribution --}}
        <x-chart-card title="Distribusi Status" subtitle="Status permintaan dana saat ini">
            <div id="status-chart" class="w-full min-h-[300px]"></div>
        </x-chart-card>

        {{-- Sumber Dana Distribution --}}
        <x-chart-card title="Penerimaan per Sumber Dana" subtitle="Penerimaan kumulatif tahun anggaran">
            <div id="radial-chart" class="w-full min-h-[300px]"></div>
        </x-chart-card>
    </div>

    {{-- Quick Stats Bar --}}
    @php
        $selisihPenerimaan = $totalPenerimaan - $totalPengeluaran;
        $selisihColor = $selisihPenerimaan >= 0 ? 'emerald' : 'red';
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-5 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
            <div class="p-2.5 rounded-xl bg-{{ $selisihColor }}-50 shrink-0">
                <x-heroicon-o-arrows-right-left class="w-5 h-5 text-{{ $selisihColor }}-500"/>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-400 font-medium">Selisih Penerimaan &minus; Pengeluaran</p>
                <p class="text-lg font-extrabold text-{{ $selisihColor }}-600">Rp {{ number_format(abs($selisihPenerimaan), 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
            <div class="p-2.5 rounded-xl bg-purple-50 shrink-0">
                <x-heroicon-o-chart-bar class="w-5 h-5 text-purple-500"/>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-400 font-medium">Persentase Realisasi</p>
                <p class="text-lg font-extrabold text-slate-800">{{ $pengeluaranPersen }}%</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center gap-4">
            <div class="p-2.5 rounded-xl bg-cyan-50 shrink-0">
                <x-heroicon-o-building-office class="w-5 h-5 text-cyan-500"/>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-400 font-medium">OPD Aktif</p>
                <p class="text-lg font-extrabold text-slate-800">{{ $topOpd->count() }} unit</p>
            </div>
        </div>
    </div>

    @php
        $statusLabels = array_keys($statusCounts->toArray());
        $statusData = array_values($statusCounts->toArray());
        $statusColors = array_map(fn($s) => match($s) {
            'draft' => '#94a3b8',
            'menunggu' => '#f59e0b',
            'disetujui' => '#22c55e',
            'ditolak' => '#ef4444',
            default => '#94a3b8',
        }, $statusLabels);

        $sumberLabels = $sumberDanaPenerimaan->keys()->all();
        $sumberData = $sumberDanaPenerimaan->map(fn($v) => round($v / 1000000000, 1))->values()->all();
        $sumberColors = ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#06b6d4', '#f43f5e', '#f97316', '#14b8a6'];
        $radialColors = array_slice($sumberColors, 0, max(count($sumberLabels), 1));
    @endphp

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const barCategories = @json($topOpd->pluck('nama'));
            const barData = @json($topOpd->pluck('total_realisasi_pengeluaran')->map(fn($v) => round($v / 1000000000, 1)));

            const barOptions = {
                series: [{ name: 'Realisasi', data: barData }],
                chart: { type: 'bar', height: 380, fontFamily: 'Instrument Sans, sans-serif', toolbar: { show: false } },
                colors: ['#0F4C81'],
                plotOptions: {
                    bar: { borderRadius: 8, borderRadiusApplication: 'end', horizontal: true, barHeight: '60%' }
                },
                xaxis: {
                    categories: barCategories,
                    labels: { style: { fontSize: '11px', colors: '#94a3b8' } }
                },
                yaxis: { labels: { style: { fontSize: '11px', colors: '#94a3b8' }, formatter: (v) => 'Rp ' + v + ' M' } },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                dataLabels: { enabled: false }
            };
            new ApexCharts(document.querySelector('#bar-chart'), barOptions).render();

            const statusOptions = {
                series: @json($statusData ?: [1]),
                chart: { type: 'donut', height: 300, fontFamily: 'Instrument Sans, sans-serif' },
                labels: @json($statusLabels ?: ['Belum ada data']),
                colors: @json($statusColors ?: ['#94a3b8']),
                plotOptions: { pie: { donut: { size: '60%' } } },
                legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 8, vertical: 4 } },
                dataLabels: { enabled: false },
                stroke: { width: 0 }
            };
            new ApexCharts(document.querySelector('#status-chart'), statusOptions).render();

            const radialOptions = {
                series: @json($sumberData ?: [1]),
                chart: { type: 'radialBar', height: 300, fontFamily: 'Instrument Sans, sans-serif' },
                labels: @json($sumberLabels ?: ['Belum ada data']),
                colors: @json($radialColors ?: ['#94a3b8']),
                plotOptions: {
                    radialBar: {
                        hollow: { size: '45%' },
                        dataLabels: { name: { fontSize: '12px' }, value: { fontSize: '14px', fontWeight: 600, formatter: (v) => 'Rp ' + v + ' M' } }
                    }
                },
                legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 8, vertical: 4 } },
                stroke: { lineCap: 'round' }
            };
            new ApexCharts(document.querySelector('#radial-chart'), radialOptions).render();
        });
    </script>
    @endpush
</x-app-layout>
