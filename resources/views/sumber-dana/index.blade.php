<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Sumber Dana" :breadcrumbs="['Sumber Dana']">
            <x-slot name="actions">
                <a href="{{ route('sumber-dana.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Sumber Dana
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Total Pagu" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase" value="{{ $persentase }}%" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-wallet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jenis Sumber Dana" value="{{ $sumberDanaTypes->count() }} Sumber" color="info">
            <x-slot name="icon">
                <x-heroicon-o-squares-2x2 class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Filter Chips + Search --}}
    <x-card class="mb-6" x-data="{ active: 'all' }">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <button
                    @click="active = 'all'"
                    :class="active === 'all' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 text-sm font-medium rounded-xl transition-all">
                    Semua
                </button>
                @foreach($sumberDanaTypes as $type)
                    <button
                        @click="active = '{{ $type }}'"
                        :class="active === '{{ $type }}' ? 'bg-primary text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-4 py-2 text-sm font-medium rounded-xl transition-all">
                        {{ $type }}
                    </button>
                @endforeach
            </div>

            <div class="lg:ml-auto flex items-center gap-2">
                <div class="relative flex-1 lg:flex-none">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                    <input type="text" placeholder="Cari sumber dana..." class="w-full lg:w-64 pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
                </div>
                <button class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-200 transition-all whitespace-nowrap">
                    <x-heroicon-o-funnel class="w-4 h-4 inline mr-1"/>
                    Filter
                </button>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 lg:gap-5">
        {{-- Main Table --}}
        <div class="xl:col-span-2">
            @php
                $colorMap = [
                    'blue' => ['chip' => 'bg-blue-50', 'dot' => 'bg-blue-500', 'bar' => 'bg-blue-500', 'row' => 'hover:bg-blue-50/40'],
                    'emerald' => ['chip' => 'bg-emerald-50', 'dot' => 'bg-emerald-500', 'bar' => 'bg-emerald-500', 'row' => 'hover:bg-emerald-50/40'],
                    'amber' => ['chip' => 'bg-amber-50', 'dot' => 'bg-amber-500', 'bar' => 'bg-amber-500', 'row' => 'hover:bg-amber-50/40'],
                    'purple' => ['chip' => 'bg-purple-50', 'dot' => 'bg-purple-500', 'bar' => 'bg-purple-500', 'row' => 'hover:bg-purple-50/40'],
                    'cyan' => ['chip' => 'bg-cyan-50', 'dot' => 'bg-cyan-500', 'bar' => 'bg-cyan-500', 'row' => 'hover:bg-cyan-50/40'],
                    'rose' => ['chip' => 'bg-rose-50', 'dot' => 'bg-rose-500', 'bar' => 'bg-rose-500', 'row' => 'hover:bg-rose-50/40'],
                    'orange' => ['chip' => 'bg-orange-50', 'dot' => 'bg-orange-500', 'bar' => 'bg-orange-500', 'row' => 'hover:bg-orange-50/40'],
                    'teal' => ['chip' => 'bg-teal-50', 'dot' => 'bg-teal-500', 'bar' => 'bg-teal-500', 'row' => 'hover:bg-teal-50/40'],
                ];
                $colorKeys = array_keys($colorMap);
            @endphp

            <x-card :padding="false">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm min-w-[850px]">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">No</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sumber Dana</th>
                                <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Pagu</th>
                                <th class="text-right px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Realisasi</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider min-w-[160px]">Persentase</th>
                                <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/80">
                            @foreach($sumberDanaRecords as $idx => $src)
                                @php
                                    $color = $colorMap[$colorKeys[($sumberDanaRecords->firstItem() + $idx - 1) % count($colorKeys)]];
                                    $pers = $src->pagu > 0 ? round(($src->realisasi / $src->pagu) * 100, 1) : 0;
                                @endphp
                                <tr class="{{ $color['row'] }} transition-colors">
                                    <td class="px-5 py-4 text-slate-400 font-medium">{{ $sumberDanaRecords->firstItem() + $idx }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 shrink-0 rounded-xl {{ $color['chip'] }} flex items-center justify-center ring-1 ring-black/5">
                                                <div class="w-2.5 h-2.5 rounded-full {{ $color['dot'] }}"></div>
                                            </div>
                                            <div class="min-w-0">
                                                <span class="text-sm font-semibold text-slate-800 block truncate">{{ $src->nama_sumber_dana }}</span>
                                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $src->opd?->nama }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        <span class="text-sm font-semibold text-emerald-600">Rp {{ number_format($src->pagu, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right whitespace-nowrap">
                                        <span class="text-sm font-semibold text-red-500">Rp {{ number_format($src->realisasi, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex-1 bg-slate-100 rounded-full h-2">
                                                <div class="{{ $color['bar'] }} h-2 rounded-full transition-all duration-500" style="width: {{ max(0, min($pers, 100)) }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-slate-500 w-10 text-right tabular-nums">{{ $pers }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('sumber-dana.edit', $src) }}"
                                                class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all"
                                                title="Edit">
                                                <x-heroicon-o-pencil class="w-4 h-4"/>
                                            </a>
                                            <form action="{{ route('sumber-dana.destroy', $src->id) }}" method="POST" class="inline" x-data
                                                  @submit.prevent="if(confirm('Yakin ingin menghapus data ini?')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                                    title="Hapus">
                                                    <x-heroicon-o-trash class="w-4 h-4"/>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 lg:px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">Menampilkan {{ $sumberDanaRecords->firstItem() }}–{{ $sumberDanaRecords->lastItem() }} dari {{ $sumberDanaRecords->total() }} sumber dana</p>
                    {{ $sumberDanaRecords->withQueryString()->links('pagination::tailwind') }}
                </div>
            </x-card>
        </div>

        {{-- Right Panel --}}
        <div class="space-y-4 lg:space-y-5">
            {{-- Quick Actions --}}
            <x-card title="Aksi Cepat">
                <div class="space-y-2">
                    <a href="{{ route('permintaan-dana.index') }}" class="flex items-center gap-3 px-4 py-3 bg-primary/5 border border-primary/10 text-primary text-sm font-semibold rounded-xl hover:bg-primary hover:text-white transition-all group">
                        <div class="p-1.5 bg-primary/10 rounded-lg group-hover:bg-white/20 transition-all">
                            <x-heroicon-o-document-text class="w-4 h-4"/>
                        </div>
                        Buat Permintaan Dana
                    </a>
                    <a href="{{ route('pengeluaran.index') }}" class="flex items-center gap-3 px-4 py-3 bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-100 transition-all border border-slate-200/80 group">
                        <div class="p-1.5 bg-slate-200/60 rounded-lg group-hover:bg-slate-300/60 transition-all">
                            <x-heroicon-o-arrow-up-right class="w-4 h-4"/>
                        </div>
                        Realisasi Pengeluaran
                    </a>
                    <a href="{{ route('laporan-posisi-kas.index') }}" class="flex items-center gap-3 px-4 py-3 bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-100 transition-all border border-slate-200/80 group">
                        <div class="p-1.5 bg-slate-200/60 rounded-lg group-hover:bg-slate-300/60 transition-all">
                            <x-heroicon-o-banknotes class="w-4 h-4"/>
                        </div>
                        Laporan Posisi Kas
                    </a>
                </div>
            </x-card>

            {{-- Top 5 OPD --}}
            <x-card title="Top 5 OPD berdasarkan Pagu">
                @php
                    $topOpd = $opds->sortByDesc('total_pagu')->take(5);
                    $maxPagu = (float) ($topOpd->first()->total_pagu ?? 0) > 0 ? (float) $topOpd->first()->total_pagu : 1;
                @endphp
                <div class="space-y-3.5">
                    @foreach($topOpd as $rank => $opd)
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 shrink-0 rounded-lg bg-primary/10 flex items-center justify-center mt-0.5">
                                <span class="text-xs font-bold text-primary">{{ $rank + 1 }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-sm font-medium text-slate-700 truncate">{{ $opd->nama }}</span>
                                    <span class="text-xs font-bold text-slate-500 whitespace-nowrap tabular-nums">Rp {{ number_format($opd->total_pagu / 1000000000, 1, ',', '.') }} M</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ max(0, min(($opd->total_pagu / $maxPagu) * 100, 100)) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-5 mt-6">
        <x-chart-card title="Grafik Posisi Kas per Sumber Dana" subtitle="Distribusi kas berdasarkan sumber dana">
            <div id="donut-chart" class="w-full min-h-[300px]"></div>
        </x-chart-card>

        <x-chart-card title="Tren Penerimaan vs Pengeluaran" subtitle="Data bulanan Januari - Desember 2026">
            <div id="line-chart" class="w-full min-h-[300px]"></div>
        </x-chart-card>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const donutData = @json($sumberDanaData->pluck('total_pagu')->map(fn($v) => round($v / 1000000000, 1)));
            const donutLabels = @json($sumberDanaData->pluck('nama_sumber_dana'));
            const donutColors = ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#06b6d4', '#f43f5e', '#f97316', '#14b8a6'];

            const donutOptions = {
                series: donutData,
                chart: { type: 'donut', height: 300, fontFamily: 'Instrument Sans, sans-serif' },
                labels: donutLabels,
                colors: donutColors,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: { show: true, label: 'Total Pagu', formatter: () => 'Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M' }
                            }
                        }
                    }
                },
                legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 8, vertical: 4 } },
                dataLabels: { enabled: false },
                stroke: { width: 0 }
            };
            const donutChart = new ApexCharts(document.querySelector('#donut-chart'), donutOptions);
            donutChart.render();

            const lineOptions = {
                series: [
                    { name: 'Penerimaan', data: [3.2, 3.8, 4.1, 3.9, 4.5, 4.2, 3.8, 4.0, 4.3, 4.1, 3.9, 4.2] },
                    { name: 'Pengeluaran', data: [2.1, 2.4, 2.8, 2.5, 3.0, 2.7, 2.4, 2.6, 2.9, 2.7, 2.5, 2.8] }
                ],
                chart: { type: 'line', height: 300, fontFamily: 'Instrument Sans, sans-serif', toolbar: { show: false } },
                colors: ['#22c55e', '#ef4444'],
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    labels: { style: { fontSize: '11px', colors: '#94a3b8' } }
                },
                yaxis: {
                    labels: { style: { fontSize: '11px', colors: '#94a3b8' }, formatter: (val) => val + ' M' }
                },
                legend: { position: 'bottom', fontSize: '12px', itemMargin: { horizontal: 8 } },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                markers: { size: 4, strokeWidth: 2 }
            };
            const lineChart = new ApexCharts(document.querySelector('#line-chart'), lineOptions);
            lineChart.render();
        });
    </script>
    @endpush
</x-app-layout>
