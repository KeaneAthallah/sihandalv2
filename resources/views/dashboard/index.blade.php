<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" :breadcrumbs="['Dashboard']" />
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Total Anggaran" value="Rp {{ number_format($totalAnggaran / 1000000000, 1, ',', '.') }} M" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Realisasi Penerimaan" value="Rp {{ number_format($totalPenerimaan / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Realisasi Pengeluaran" value="Rp {{ number_format($totalPengeluaran / 1000000000, 1, ',', '.') }} M" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Permintaan Pending" value="{{ $permintaanPending }}" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Sisa Kas --}}
    <div class="mb-6">
        @php
            $sisaKasPersentase = $sisaKasMax > 0 ? min(round(($sisaKas / $sisaKasMax) * 100), 100) : 0;
        @endphp
        <x-progress-card amount="Rp {{ number_format($sisaKas, 0, ',', '.') }}" :percentage="$sisaKasPersentase" color="success"/>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-5 mb-6">
        {{-- Main Chart --}}
        <div class="lg:col-span-2">
            <x-chart-card title="Realisasi Anggaran per OPD" subtitle="Top OPD berdasarkan realisasi pengeluaran">
                <div id="bar-chart" class="w-full min-h-[350px]"></div>
            </x-chart-card>
        </div>

        {{-- Recent Activity --}}
        <x-card title="Aktivitas Terbaru">
            <div class="space-y-4">
                @forelse($recentPermintaan as $item)
                    @php
                        $activity = match($item->status) {
                            'draft' => ['icon' => 'document-text', 'classes' => 'bg-slate-50 text-slate-500', 'text' => 'dibuat'],
                            'menunggu' => ['icon' => 'clock', 'classes' => 'bg-amber-50 text-amber-500', 'text' => 'diajukan'],
                            'disetujui' => ['icon' => 'check-circle', 'classes' => 'bg-emerald-50 text-emerald-500', 'text' => 'disetujui'],
                            'ditolak' => ['icon' => 'x-circle', 'classes' => 'bg-red-50 text-red-500', 'text' => 'ditolak'],
                            default => ['icon' => 'document-text', 'classes' => 'bg-slate-50 text-slate-500', 'text' => $item->status],
                        };
                    @endphp
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg {{ $activity['classes'] }} flex items-center justify-center shrink-0 mt-0.5">
                            <x-dynamic-component :component="'heroicon-o-'.$activity['icon']" class="w-4 h-4"/>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 leading-snug">{{ $item->nomor_permintaan }} {{ $activity['text'] }} oleh {{ $item->opd->nama ?? 'OPD' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $item->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-4">Belum ada aktivitas</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5">
        {{-- Status Distribution --}}
        <x-chart-card title="Status Permintaan Dana" subtitle="Distribusi status permintaan saat ini">
            <div id="status-chart" class="w-full min-h-[280px]"></div>
        </x-chart-card>

        {{-- Sumber Dana Distribution --}}
        <x-chart-card title="Penerimaan per Sumber Dana" subtitle="Penerimaan kumulatif tahun 2026">
            <div id="radial-chart" class="w-full min-h-[280px]"></div>
        </x-chart-card>
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
                chart: { type: 'bar', height: 350, fontFamily: 'Instrument Sans, sans-serif', toolbar: { show: false } },
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
                chart: { type: 'donut', height: 280, fontFamily: 'Instrument Sans, sans-serif' },
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
                chart: { type: 'radialBar', height: 280, fontFamily: 'Instrument Sans, sans-serif' },
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
