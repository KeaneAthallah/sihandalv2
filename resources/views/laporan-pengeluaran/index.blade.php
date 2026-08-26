<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Pengeluaran" :breadcrumbs="['Laporan', 'Pengeluaran']" />
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <x-stat-card title="Total Pengeluaran" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="+8.2%" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Anggaran" value="Rp {{ number_format($totalAnggaran / 1000000000, 1, ',', '.') }} M" change="+5.1%" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-calculator class="w-6 h-6"/>
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

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-800">Laporan Realisasi Pengeluaran</h3>
            <p class="text-xs text-slate-400 mt-0.5">Rekapitulasi pengeluaran per OPD</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kegiatan</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Anggaran</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Realisasi</th>
                        <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($pengeluarans as $idx => $item)
                        @php
                            $barColor = match(true) {
                                $item->persentase >= 95 => 'bg-emerald-500',
                                $item->persentase >= 80 => 'bg-blue-500',
                                $item->persentase >= 60 => 'bg-amber-500',
                                default => 'bg-red-500',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700 font-medium">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $item->nama_kegiatan ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600 text-right font-mono">
                                Rp {{ number_format($item->anggaran, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-primary text-right font-mono">
                                Rp {{ number_format($item->realisasi, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 bg-slate-100 rounded-full h-2 min-w-[80px]">
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ min($item->persentase, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-600 w-12 text-right">{{ $item->persentase }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $pengeluarans->count() }} data</p>
        </div>
    </div>
</x-app-layout>
