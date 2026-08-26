<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" :breadcrumbs="['Dashboard']">
            <x-slot name="actions">
                <span class="text-xs text-slate-400 font-medium">Tahun Anggaran {{ date('Y') }}</span>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $anggaranFormatted = number_format($totalAnggaran / 1000000000, 2, ',', '.');
        $penerimaanFormatted = number_format($totalPenerimaan / 1000000000, 2, ',', '.');
        $pengeluaranFormatted = number_format($totalPengeluaran / 1000000000, 2, ',', '.');
        $penerimaanPersen = $totalAnggaran > 0 ? round(($totalPenerimaan / $totalAnggaran) * 100, 1) : 0;
        $pengeluaranPersen = $totalAnggaran > 0 ? round(($totalPengeluaran / $totalAnggaran) * 100, 1) : 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Total Anggaran</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight">Rp {{ $anggaranFormatted }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar Rupiah</p>
                </div>
                <div class="p-2.5 rounded-lg bg-slate-100 shrink-0">
                    <x-heroicon-o-banknotes class="w-6 h-6 text-slate-600"/>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Realisasi Penerimaan</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-emerald-600 tracking-tight">Rp {{ $penerimaanFormatted }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar &middot; {{ $penerimaanPersen }}% dari anggaran</p>
                </div>
                <div class="p-2.5 rounded-lg bg-emerald-50 shrink-0">
                    <x-heroicon-o-arrow-down-left class="w-6 h-6 text-emerald-500"/>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Realisasi Pengeluaran</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-red-500 tracking-tight">Rp {{ $pengeluaranFormatted }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar &middot; {{ $pengeluaranPersen }}% dari anggaran</p>
                </div>
                <div class="p-2.5 rounded-lg bg-red-50 shrink-0">
                    <x-heroicon-o-arrow-up-right class="w-6 h-6 text-red-500"/>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500">Permintaan Pending</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-amber-500 tracking-tight">{{ number_format($permintaanPending) }}</p>
                    <p class="text-xs text-slate-400 mt-1">permintaan menunggu verifikasi</p>
                </div>
                <div class="p-2.5 rounded-lg bg-amber-50 shrink-0">
                    <x-heroicon-o-clock class="w-6 h-6 text-amber-500"/>
                </div>
            </div>
        </div>
    </div>

    @php
        $sisaKasPersentase = $sisaKasMax > 0 ? min(round(($sisaKas / $sisaKasMax) * 100), 100) : 0;
        $sisaKasColor = $sisaKasPersentase >= 60 ? 'success' : ($sisaKasPersentase >= 30 ? 'warning' : 'danger');
    @endphp
    <x-progress-card
        title="Sisa Kas"
        amount="Rp {{ number_format($sisaKas, 0, ',', '.') }}"
        :percentage="$sisaKasPersentase"
        color="{{ $sisaKasColor }}"
        class="mb-5"
    />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        <div class="lg:col-span-3">
            <x-chart-card title="Top OPD Realisasi" subtitle="Realisasi pengeluaran per organisasi" class="rounded-lg">
                <div id="bar-chart" class="w-full min-h-[380px]"></div>
            </x-chart-card>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
        <x-chart-card title="Distribusi Status" subtitle="Status permintaan dana saat ini" class="rounded-lg">
            <div id="status-chart" class="w-full min-h-[300px]"></div>
        </x-chart-card>

        <x-chart-card title="Penerimaan per Sumber Dana" subtitle="Penerimaan kumulatif tahun anggaran" class="rounded-lg">
            <div id="radial-chart" class="w-full min-h-[300px]"></div>
        </x-chart-card>
    </div>

    @if(isset($recentPermintaan) && $recentPermintaan->count())
    <x-card :padding="false" title="Transaksi Terbaru" subtitle="Riwayat permintaan dana" class="mb-5">
        <x-slot name="actions">
            <span class="px-2.5 py-1 bg-slate-100 text-slate-500 text-xs font-semibold rounded-lg">{{ $recentPermintaan->count() }}</span>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Nomor</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">OPD</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Status</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentPermintaan as $item)
                        @php
                            $statusBadge = match($item->status) {
                                'draft' => 'bg-slate-100 text-slate-600',
                                'menunggu' => 'bg-amber-50 text-amber-700',
                                'disetujui' => 'bg-emerald-50 text-emerald-700',
                                'ditolak' => 'bg-red-50 text-red-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                            $statusLabel = match($item->status) {
                                'draft' => 'Dibuat',
                                'menunggu' => 'Diajukan',
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                                default => $item->status,
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $item->nomor_permintaan }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $item->opd->nama ?? 'OPD' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-400 text-xs">{{ $item->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada transaksi terbaru</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
    @endif

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

            new ApexCharts(document.querySelector('#bar-chart'), {
                series: [{ name: 'Realisasi', data: barData }],
                chart: { type: 'bar', height: 380, fontFamily: 'Instrument Sans, sans-serif', toolbar: { show: false } },
                colors: ['#0F4C81'],
                plotOptions: {
                    bar: { borderRadius: 6, borderRadiusApplication: 'end', horizontal: true, barHeight: '60%' }
                },
                xaxis: {
                    categories: barCategories,
                    labels: { style: { fontSize: '11px', colors: '#94a3b8' } }
                },
                yaxis: { labels: { style: { fontSize: '11px', colors: '#94a3b8' }, formatter: (v) => 'Rp ' + v + ' M' } },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                dataLabels: { enabled: false }
            }).render();

            new ApexCharts(document.querySelector('#status-chart'), {
                series: @json($statusData ?: [1]),
                chart: { type: 'donut', height: 300, fontFamily: 'Instrument Sans, sans-serif' },
                labels: @json($statusLabels ?: ['Belum ada data']),
                colors: @json($statusColors ?: ['#94a3b8']),
                plotOptions: { pie: { donut: { size: '60%' } } },
                legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 8, vertical: 4 } },
                dataLabels: { enabled: false },
                stroke: { width: 0 }
            }).render();

            new ApexCharts(document.querySelector('#radial-chart'), {
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
            }).render();
        });
    </script>
    @endpush
</x-app-layout>
