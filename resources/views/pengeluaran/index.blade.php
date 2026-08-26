<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengeluaran" :breadcrumbs="['Keuangan', 'Pengeluaran']">
            <x-slot name="actions">
                <a href="{{ route('pengeluaran.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Pengeluaran
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Total Pengeluaran" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="+8.2%" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Anggaran" value="Rp {{ number_format($totalAnggaran / 1000000000, 1, ',', '.') }} M" change="+3.1%" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-calendar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase" value="{{ $persentase }}%" change="dari anggaran" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Belum Direalisasi" value="Rp {{ number_format(($totalAnggaran - $totalRealisasi) / 1000000000, 1, ',', '.') }} M" change="-1.2 M dari target" changeType="down" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-6">
        <div class="flex items-center gap-3 flex-wrap" x-data="{ active: 'all' }">
            @php
                $chips = [
                    ['key' => 'all', 'label' => 'Semua'],
                    ['key' => 'realisasi', 'label' => 'Realisasi'],
                    ['key' => 'pending', 'label' => 'Pending'],
                ];
            @endphp
            @foreach($chips as $chip)
                <button
                    @click="active = '{{ $chip['key'] }}'"
                    :class="active === '{{ $chip['key'] }}' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 text-sm font-medium rounded-xl transition-all">
                    {{ $chip['label'] }}
                </button>
            @endforeach

            <div class="ml-auto flex items-center gap-2 flex-wrap">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                    <input type="text" placeholder="Cari SP2D, OPD..." class="pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 w-48 lg:w-56 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        {{-- Main Table --}}
        <div class="xl:col-span-2">
            <x-card>
                <x-slot name="header">
                    <h3 class="text-sm font-semibold text-slate-800">Daftar Pengeluaran</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Data realisasi pengeluaran</p>
                </x-slot>

                <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
                    <table class="w-full text-sm min-w-[950px]">
                        <thead>
                            <tr class="bg-slate-50/70">
                                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kegiatan</th>
                                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                                <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sumber Dana</th>
                                <th class="text-right px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Anggaran</th>
                                <th class="text-right px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Realisasi</th>
                                <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($pengeluarans as $idx => $item)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-4 text-slate-500">{{ $idx + 1 }}</td>
                                    <td class="px-4 py-4 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-4 py-4">
                                        <span class="text-sm font-medium text-slate-800">{{ $item->nama_kegiatan ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-4 text-slate-700 max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200 whitespace-nowrap">
                                            {{ $item->sumber_dana }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 font-semibold text-slate-700 text-right whitespace-nowrap">
                                        Rp {{ number_format($item->anggaran / 1000000000, 1, ',', '.') }} M
                                    </td>
                                    <td class="px-4 py-4 font-semibold text-red-500 text-right whitespace-nowrap">
                                        Rp {{ number_format($item->realisasi / 1000000000, 1, ',', '.') }} M
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('pengeluaran.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                                <x-heroicon-o-pencil class="w-4 h-4"/>
                                            </a>
                                            <form method="POST" action="{{ route('pengeluaran.destroy', $item) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus pengeluaran ini?')) $el.submit()">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                                    <x-heroicon-o-trash class="w-4 h-4"/>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-14 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                            <p class="text-sm text-slate-500">Belum ada data pengeluaran.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100">
                    <p class="text-sm text-slate-500">Menampilkan {{ $pengeluarans->count() }} pengeluaran</p>
                </div>
            </x-card>
        </div>

        {{-- Right Panel --}}
        <div class="space-y-6">
            <x-card title="Ringkasan">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Total Anggaran</span>
                        <span class="text-sm font-semibold text-slate-800">Rp {{ number_format($totalAnggaran / 1000000000, 1, ',', '.') }} M</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Total Realisasi</span>
                        <span class="text-sm font-semibold text-red-500">Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Persentase</span>
                        <span class="text-sm font-semibold text-primary">{{ $persentase }}%</span>
                    </div>
                    <div class="pt-3 border-t border-slate-100">
                        <x-progress-card title="Persentase Realisasi" :percentage="min(round($persentase), 100)" color="info" />
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Chart --}}
    <x-chart-card title="Pengeluaran Bulanan" subtitle="Realisasi pengeluaran per bulan tahun 2026">
        <div id="monthly-chart" class="w-full min-h-[350px]"></div>
    </x-chart-card>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const barOptions = {
                series: [{
                    name: 'Realisasi',
                    data: [2.1, 2.4, 2.8, 2.5, 3.0, 2.7, 2.6]
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'Instrument Sans, sans-serif',
                    toolbar: { show: false }
                },
                colors: ['#0F4C81'],
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        borderRadiusApplication: 'end',
                        horizontal: false,
                        columnWidth: '55%',
                    }
                },
                xaxis: {
                    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
                    labels: { style: { fontSize: '12px', colors: '#94a3b8' } }
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '11px', colors: '#94a3b8' },
                        formatter: (v) => 'Rp ' + v + ' M'
                    }
                },
                grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                dataLabels: { enabled: false }
            };
            new ApexCharts(document.querySelector('#monthly-chart'), barOptions).render();
        });
    </script>
    @endpush
</x-app-layout>
