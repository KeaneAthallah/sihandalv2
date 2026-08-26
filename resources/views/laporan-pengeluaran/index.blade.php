<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Pengeluaran" :breadcrumbs="['Laporan', 'Pengeluaran']">
            <x-slot name="actions">
                <a href="{{ route('laporan-pengeluaran.export') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                    Export CSV
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Report Header --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-6 p-6 print:shadow-none print:border-none">
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">Laporan Realisasi Pengeluaran</h2>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi pengeluaran dan realisasi anggaran per OPD</p>
        </div>
        <div class="flex items-center justify-center gap-6 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-calendar class="w-3.5 h-3.5"/>
                Periode: {{ now()->translatedFormat('F Y') }}
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-building-office-2 class="w-3.5 h-3.5"/>
                {{ $pengeluarans->pluck('opd_id')->unique()->count() }} OPD
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-document-text class="w-3.5 h-3.5"/>
                {{ $pengeluarans->count() }} Data
            </span>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="Pengeluaran tercatat" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Anggaran" value="Rp {{ number_format($totalAnggaran / 1000000000, 1, ',', '.') }} M" change="Anggaran tersedia" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-calculator class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase Realisasi" value="{{ $persentase }}%" change="dari anggaran" changeType="{{ $persentase >= 80 ? 'up' : 'down' }}" color="{{ $persentase >= 80 ? 'success' : 'warning' }}">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Sisa Anggaran" value="Rp {{ number_format(($totalAnggaran - $totalRealisasi) / 1000000000, 1, ',', '.') }} M" change="Belum direalisasi" changeType="down" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border print:border-slate-300">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Detail Laporan Pengeluaran</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Realisasi pengeluaran per OPD dan kegiatan</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> ≥95%
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> ≥80%
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> ≥60%
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> &lt;60%
                    </span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-slate-200 bg-slate-50/80">
                        <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider w-10">No</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-28">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">OPD</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider w-40">Anggaran (Rp)</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider w-40">Realisasi (Rp)</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider w-40">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pengeluarans as $idx => $item)
                        @php
                            $barColor = match(true) {
                                $item->persentase >= 95 => 'bg-emerald-500',
                                $item->persentase >= 80 => 'bg-blue-500',
                                $item->persentase >= 60 => 'bg-amber-500',
                                default => 'bg-red-500',
                            };
                            $barTrackColor = match(true) {
                                $item->persentase >= 95 => 'bg-emerald-100',
                                $item->persentase >= 80 => 'bg-blue-100',
                                $item->persentase >= 60 => 'bg-amber-100',
                                default => 'bg-red-100',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-medium text-slate-800">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $item->nama_kegiatan ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-right text-slate-600 font-mono text-xs">
                                Rp {{ number_format($item->anggaran, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-semibold text-slate-800 font-mono text-xs">
                                Rp {{ number_format($item->realisasi, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 {{ $barTrackColor }} rounded-full h-2 min-w-[60px] max-w-[100px]">
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all duration-300" style="width: {{ min($item->persentase, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600 w-11 text-right tabular-nums">{{ $item->persentase }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    <p class="text-sm text-slate-400">Belum ada data pengeluaran</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3.5 border-t border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <p class="text-xs text-slate-500">Menampilkan {{ $pengeluarans->count() }} data pengeluaran</p>
            <p class="text-xs text-slate-400">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
</x-app-layout>
