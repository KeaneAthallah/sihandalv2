<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" :breadcrumbs="['Dashboard']">
            <x-slot name="actions">
                <span class="text-xs text-slate-400 font-medium">Tahun Anggaran {{ $tahunAnggaran->tahun ?? date('Y') }}</span>
            </x-slot>
        </x-page-header>
    </x-slot>

    @php
        $miliar = fn (float $value) => number_format($value / 1000000000, 2, ',', '.');
        $rupiah = fn (float $value) => number_format($value, 0, ',', '.');

        $pagu = (float) $budget['pagu'];
        $realisasiBelanja = (float) $budget['realisasi'];
        $commit = (float) $budget['commit'];
        $available = (float) $budget['available'];
        $kasPenerimaan = (float) $kas['kas_penerimaan'];
        $kasPengeluaran = (float) $kas['kas_pengeluaran'];
        $saldoEfektif = (float) $kas['saldo_efektif'];

        $penerimaanPersen = $penerimaanTarget > 0 ? round($kasPenerimaan / $penerimaanTarget * 100, 1) : 0;
        $pengeluaranPersen = $pagu > 0 ? round($realisasiBelanja / $pagu * 100, 1) : 0;
        $saldoPersen = $pagu > 0 ? (int) min(round($saldoEfektif / $pagu * 100), 100) : 0;
        $saldoColor = $saldoPersen >= 60 ? 'success' : ($saldoPersen >= 30 ? 'warning' : 'danger');
    @endphp

    {{-- KPI row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <div class="card p-5">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="stat-label">Total Pagu</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-slate-800 tracking-tight stat-value">Rp {{ $miliar($pagu) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar Rupiah</p>
                </div>
                <div class="p-2.5 rounded-xl bg-slate-100 shrink-0 ring-1 ring-inset ring-black/[0.03]">
                    <x-heroicon-o-banknotes class="w-6 h-6 text-slate-600"/>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="stat-label">Realisasi Penerimaan</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-emerald-600 tracking-tight stat-value">Rp {{ $miliar($kasPenerimaan) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar &middot; {{ $penerimaanPersen }}% dari target</p>
                </div>
                <div class="p-2.5 rounded-xl bg-emerald-50 shrink-0 ring-1 ring-inset ring-emerald-500/10">
                    <x-heroicon-o-arrow-down-left class="w-6 h-6 text-emerald-500"/>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="stat-label">Realisasi Pengeluaran</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-red-500 tracking-tight stat-value">Rp {{ $miliar($realisasiBelanja) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar &middot; {{ $pengeluaranPersen }}% dari pagu</p>
                </div>
                <div class="p-2.5 rounded-xl bg-red-50 shrink-0 ring-1 ring-inset ring-red-500/10">
                    <x-heroicon-o-arrow-up-right class="w-6 h-6 text-red-500"/>
                </div>
            </div>
        </div>

        <div class="card p-5">
            <div class="flex items-start justify-between">
                <div class="min-w-0">
                    <p class="stat-label">Pagu Tersedia</p>
                    <p class="mt-2 text-2xl lg:text-3xl font-extrabold text-primary tracking-tight stat-value">Rp {{ $miliar($available) }}</p>
                    <p class="text-xs text-slate-400 mt-1">Miliar &middot; Rp {{ $rupiah($commit) }} di-commit</p>
                </div>
                <div class="p-2.5 rounded-xl bg-primary/10 shrink-0 ring-1 ring-inset ring-primary/10">
                    <x-heroicon-o-wallet class="w-6 h-6 text-primary"/>
                </div>
            </div>
        </div>
    </div>

    {{-- Saldo kas efektif --}}
    <div class="mb-5">
        <x-progress-card
            title="Saldo Kas Efektif"
            amount="Rp {{ $rupiah($saldoEfektif) }}"
            :percentage="$saldoPersen"
            color="{{ $saldoColor }}"
        />
    </div>

    {{-- Tren + distribusi status --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">
        <x-chart-card title="Tren Bulanan" subtitle="Penerimaan vs pengeluaran per bulan" class="lg:col-span-2">
            <div id="trend-chart" class="w-full min-h-[320px]"></div>
        </x-chart-card>

        <x-chart-card title="Distribusi Status" subtitle="Status permintaan dana saat ini">
            <div id="status-chart" class="w-full min-h-[320px]"></div>
        </x-chart-card>
    </div>

    @if($isAdmin)
        {{-- Admin: top OPD + kas per sumber dana --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
            <x-chart-card title="Top OPD Realisasi" subtitle="Realisasi pengeluaran per organisasi">
                <div id="bar-chart" class="w-full min-h-[340px]"></div>
            </x-chart-card>

            <x-card :padding="false" title="Kas per Sumber Dana" subtitle="Kas masuk, keluar, dan saldo seluruh OPD">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-5 py-3 table-head">Sumber Dana</th>
                                <th class="px-5 py-3 table-head text-right">Masuk</th>
                                <th class="px-5 py-3 table-head text-right">Keluar</th>
                                <th class="px-5 py-3 table-head text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($kasPerSumberDana as $row)
                                <tr class="table-row">
                                    <td class="px-5 py-3.5 font-medium text-slate-700">{{ $row['nama'] }}</td>
                                    <td class="px-5 py-3.5 text-right money text-emerald-600">{{ $rupiah((float) $row['masuk']) }}</td>
                                    <td class="px-5 py-3.5 text-right money text-red-500">{{ $rupiah((float) $row['keluar']) }}</td>
                                    <td class="px-5 py-3.5 text-right money font-semibold text-slate-800">{{ $rupiah((float) $row['saldo']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada aktivitas kas per sumber dana</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    @else
        {{-- OPD: kas per sumber dana --}}
        <div class="mb-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-slate-700">Kas Saya per Sumber Dana</h2>
                <span class="text-xs text-slate-400">Setelah kuota penerimaan per sumber dana</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($kasPerSumberDana as $row)
                    @php
                        $masuk = (float) $row['masuk'];
                        $keluar = (float) $row['keluar'];
                        $transferNet = (float) $row['transfer_net'];
                        $diCommit = (float) $row['di_commit'];
                        $saldoEfektifRow = (float) $row['saldo_efektif'];
                        $terpakaiPersen = $masuk > 0 ? (int) min(round($keluar / $masuk * 100), 100) : 0;
                    @endphp
                    <div class="card p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $row['nama'] }}</p>
                            <span class="text-xs text-slate-400 shrink-0">{{ $terpakaiPersen }}% terpakai</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Kuota penerimaan {{ number_format((float) $row['kuota_persen'], 0) }}%</p>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mt-2 mb-3">
                            <div class="bg-primary h-2 rounded-full" style="width: {{ $terpakaiPersen }}%"></div>
                        </div>
                        <dl class="space-y-1.5 text-sm">
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-400">Masuk</dt>
                                <dd class="money text-emerald-600">{{ $rupiah($masuk) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-400">Keluar</dt>
                                <dd class="money text-red-500">{{ $rupiah($keluar) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-400">Transfer net</dt>
                                <dd class="money text-slate-600">{{ $rupiah($transferNet) }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-slate-400">Di-commit</dt>
                                <dd class="money text-amber-600">{{ $rupiah($diCommit) }}</dd>
                            </div>
                            <div class="flex items-center justify-between pt-1.5 border-t border-slate-100">
                                <dt class="font-medium text-slate-600">Saldo efektif</dt>
                                <dd class="money font-bold text-slate-800">{{ $rupiah($saldoEfektifRow) }}</dd>
                            </div>
                        </dl>
                    </div>
                @empty
                    <div class="card sm:col-span-2 xl:col-span-3">
                        <div class="empty-state">
                            <div class="empty-icon">
                                <x-heroicon-o-wallet class="w-7 h-7"/>
                            </div>
                            <p class="empty-title">Belum ada aktivitas kas</p>
                            <p class="empty-desc">Kas per sumber dana akan tampil setelah ada penerimaan atau pengeluaran.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Realisasi per program --}}
    <x-card :padding="false" title="Realisasi per Program" subtitle="Program dengan pagu terbesar" class="mb-5">
        <x-slot name="actions">
            <a href="{{ route('program-kegiatan.index') }}" class="btn-ghost !px-3 !py-1.5 text-xs">Lihat Program</a>
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-5 py-3 table-head">Kode</th>
                        <th class="px-5 py-3 table-head">Program</th>
                        <th class="px-5 py-3 table-head text-right">Pagu</th>
                        <th class="px-5 py-3 table-head text-right">Realisasi</th>
                        <th class="px-5 py-3 table-head w-[180px]">Capaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($programTotals as $program)
                        <tr class="table-row">
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-mono font-semibold text-primary whitespace-nowrap">{{ $program['kode_program'] }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('program-kegiatan.index') }}" class="font-medium text-slate-700 hover:text-primary">{{ $program['nama_program'] ?? '-' }}</a>
                            </td>
                            <td class="px-5 py-3.5 text-right money text-slate-700">{{ $rupiah((float) $program['pagu']) }}</td>
                            <td class="px-5 py-3.5 text-right money text-slate-700">{{ $rupiah((float) $program['realisasi']) }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-primary h-2 rounded-full" style="width: {{ (int) min($program['percentage'], 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500 tabular-nums w-12 text-right">{{ $program['percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada data program dengan pagu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- Aktivitas terbaru --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-5">
        <x-card :padding="false" title="Permintaan Dana Terbaru" subtitle="Riwayat permintaan dana">
            <x-slot name="actions">
                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 text-xs font-semibold rounded-lg">{{ $recentPermintaan->count() }}</span>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3 table-head">Nomor</th>
                            @if($isAdmin)
                                <th class="px-5 py-3 table-head">OPD</th>
                            @endif
                            <th class="px-5 py-3 table-head text-right">Jumlah</th>
                            <th class="px-5 py-3 table-head">Status</th>
                            <th class="px-5 py-3 table-head">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentPermintaan as $item)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 font-medium text-slate-700 whitespace-nowrap">{{ $item->nomor_permintaan }}</td>
                                @if($isAdmin)
                                    <td class="px-5 py-3.5 text-slate-500 truncate max-w-[160px]">{{ $item->opd->nama ?? 'OPD' }}</td>
                                @endif
                                <td class="px-5 py-3.5 text-right money text-slate-700">{{ $rupiah((float) $item->jumlah) }}</td>
                                <td class="px-5 py-3.5"><x-status-badge :status="$item->status" /></td>
                                <td class="px-5 py-3.5 text-slate-400 text-xs whitespace-nowrap">{{ $item->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 5 : 4 }}" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada permintaan dana</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card :padding="false" title="Transfer Dana Terbaru" subtitle="Perpindahan kas antar sumber dana">
            <x-slot name="actions">
                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 text-xs font-semibold rounded-lg">{{ $recentTransfer->count() }}</span>
            </x-slot>

            <div class="divide-y divide-slate-100">
                @forelse($recentTransfer as $transfer)
                    <div class="px-5 py-3.5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-slate-700 text-sm">{{ $transfer->nomor_transfer }}</span>
                                <x-status-badge :status="$transfer->status" />
                            </div>
                            <p class="text-xs text-slate-400 mt-1 truncate">
                                {{ $transfer->sumberDanaPengirim?->nama_sumber_dana ?? '-' }}
                                <span class="text-slate-300">&rarr;</span>
                                {{ $transfer->sumberDanaPenerima?->nama_sumber_dana ?? '-' }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="money text-sm font-semibold text-slate-800">{{ $rupiah((float) $transfer->jumlah) }}</p>
                            <p class="text-xs text-slate-400">{{ $transfer->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center text-sm text-slate-400">Belum ada transfer dana</div>
                @endforelse
            </div>
        </x-card>
    </div>

    @php
        $statusLabels = ['draft' => 'Draft', 'menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
        $statusColors = ['draft' => '#94a3b8', 'menunggu' => '#f59e0b', 'disetujui' => '#22c55e', 'ditolak' => '#ef4444'];
        $statusChartLabels = [];
        $statusChartData = [];
        $statusChartColors = [];
        foreach ($permintaanCounts as $status => $count) {
            if (($count ?? 0) > 0) {
                $statusChartLabels[] = $statusLabels[$status] ?? ucfirst((string) $status);
                $statusChartData[] = (int) $count;
                $statusChartColors[] = $statusColors[$status] ?? '#94a3b8';
            }
        }
    @endphp

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toMiliar = (value) => Math.round((value / 1000000000) * 100) / 100;

            const trendEl = document.querySelector('#trend-chart');
            if (trendEl) {
                new ApexCharts(trendEl, {
                    series: [
                        { name: 'Penerimaan', data: @json($trenBulanan['penerimaan']).map(toMiliar) },
                        { name: 'Pengeluaran', data: @json($trenBulanan['pengeluaran']).map(toMiliar) }
                    ],
                    chart: { type: 'area', height: 320, fontFamily: 'Instrument Sans, sans-serif', toolbar: { show: false } },
                    colors: ['#10b981', '#ef4444'],
                    stroke: { curve: 'smooth', width: 2 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] } },
                    xaxis: { categories: @json($trenBulanan['labels']), labels: { style: { fontSize: '11px', colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { fontSize: '11px', colors: '#94a3b8' }, formatter: (v) => 'Rp ' + v + ' M' } },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                    dataLabels: { enabled: false },
                    legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px' }
                }).render();
            }

            const statusEl = document.querySelector('#status-chart');
            if (statusEl) {
                new ApexCharts(statusEl, {
                    series: @json($statusChartData ?: [1]),
                    chart: { type: 'donut', height: 320, fontFamily: 'Instrument Sans, sans-serif' },
                    labels: @json($statusChartLabels ?: ['Belum ada data']),
                    colors: @json($statusChartColors ?: ['#94a3b8']),
                    plotOptions: { pie: { donut: { size: '60%' } } },
                    legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 8, vertical: 4 } },
                    dataLabels: { enabled: false },
                    stroke: { width: 0 }
                }).render();
            }

            @if($isAdmin)
            const barEl = document.querySelector('#bar-chart');
            if (barEl) {
                new ApexCharts(barEl, {
                    series: [{ name: 'Realisasi', data: @json($topOpd->pluck('total_realisasi_pengeluaran')->map(fn ($v) => round($v / 1000000000, 1))) }],
                    chart: { type: 'bar', height: 340, fontFamily: 'Instrument Sans, sans-serif', toolbar: { show: false } },
                    colors: ['#0F4C81'],
                    plotOptions: { bar: { borderRadius: 6, borderRadiusApplication: 'end', horizontal: true, barHeight: '60%' } },
                    xaxis: { categories: @json($topOpd->pluck('nama')), labels: { style: { fontSize: '11px', colors: '#94a3b8' } } },
                    yaxis: { labels: { style: { fontSize: '11px', colors: '#94a3b8' }, formatter: (v) => 'Rp ' + v + ' M' } },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                    dataLabels: { enabled: false }
                }).render();
            }
            @endif
        });
    </script>
    @endpush
</x-app-layout>
