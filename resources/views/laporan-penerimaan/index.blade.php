<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Penerimaan" :breadcrumbs="['Laporan', 'Penerimaan']">
            <x-slot name="actions">
                <a href="{{ route('laporan-penerimaan.export') }}" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 inline mr-1"/>
                    Export CSV
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Report Header --}}
    <div class="bg-white rounded-xl border border-slate-200 mb-6 p-6">
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">Laporan Realisasi Penerimaan</h2>
            <p class="text-sm text-slate-500 mt-1">Rekapitulasi target dan realisasi penerimaan daerah</p>
        </div>
        <div class="flex items-center justify-center gap-6 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-calendar class="w-3.5 h-3.5"/>
                Periode: {{ now()->translatedFormat('F Y') }}
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-building-office-2 class="w-3.5 h-3.5"/>
                {{ $penerimaans->pluck('opd_id')->unique()->count() }} OPD
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-document-text class="w-3.5 h-3.5"/>
                {{ $penerimaans->count() }} Data
            </span>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="Penerimaan terkumpul" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Target" value="Rp {{ number_format($totalTarget / 1000000000, 1, ',', '.') }} M" change="Target penerimaan" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-calculator class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase Tercapai" value="{{ $persentase }}%" change="dari target {{ $persentase >= 100 ? '(tercapai)' : '' }}" changeType="{{ $persentase >= 80 ? 'up' : 'down' }}" color="info">
            <x-slot name="icon">
                <x-heroicon-o-arrow-trending-up class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Sisa Selisih" value="{{ ($totalTarget - $totalRealisasi) >= 0 ? '-Rp ' . number_format(abs($totalTarget - $totalRealisasi) / 1000000000, 1, ',', '.') . ' M' : '+Rp ' . number_format(abs($totalTarget - $totalRealisasi) / 1000000000, 1, ',', '.') . ' M' }}" change="{{ ($totalTarget - $totalRealisasi) >= 0 ? 'Belum tercapai' : 'Melebihi target' }}" changeType="{{ ($totalTarget - $totalRealisasi) >= 0 ? 'down' : 'up' }}" color="{{ ($totalTarget - $totalRealisasi) >= 0 ? 'warning' : 'success' }}">
            <x-slot name="icon">
                <x-heroicon-o-calculator class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="card-title">Detail Laporan Penerimaan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Rekapitulasi target dan realisasi penerimaan per OPD</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> ≥100%
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
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr>
                        <th class="px-5 py-3 table-head text-center w-10">No</th>
                        <th class="px-5 py-3 table-head text-left w-28">Tanggal</th>
                        <th class="px-5 py-3 table-head text-left">OPD</th>
                        <th class="px-5 py-3 table-head text-left w-40">Sumber Dana</th>
                        <th class="px-5 py-3 table-head text-right w-36">Target (Rp)</th>
                        <th class="px-5 py-3 table-head text-right w-36">Realisasi (Rp)</th>
                        <th class="px-5 py-3 table-head text-center w-36">Persentase</th>
                        <th class="px-5 py-3 table-head text-right w-36">Selisih (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($penerimaans as $idx => $item)
                        @php
                            $selisih = $item->realisasi - $item->target;
                            $barColor = match(true) {
                                $item->persentase >= 100 => 'bg-emerald-500',
                                $item->persentase >= 80 => 'bg-blue-500',
                                $item->persentase >= 60 => 'bg-amber-500',
                                default => 'bg-red-500',
                            };
                            $barTrackColor = match(true) {
                                $item->persentase >= 100 => 'bg-emerald-100',
                                $item->persentase >= 80 => 'bg-blue-100',
                                $item->persentase >= 60 => 'bg-amber-100',
                                default => 'bg-red-100',
                            };
                        @endphp
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">{{ $item->opd?->nama ?? 'Provinsi' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $item->sumberDana?->nama_sumber_dana ?? $item->nama_sumber_dana ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-slate-600 font-mono text-xs">
                                Rp {{ number_format($item->target / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-slate-800 font-mono text-xs">
                                Rp {{ number_format($item->realisasi / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 {{ $barTrackColor }} rounded-full h-2 min-w-[60px] max-w-[100px]">
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all duration-300" style="width: {{ min($item->persentase, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-600 w-11 text-right tabular-nums">{{ $item->persentase }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold font-mono text-xs {{ $selisih >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $selisih >= 0 ? '+' : '' }}Rp {{ number_format($selisih / 1000000000, 1, ',', '.') }} M
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon"><x-heroicon-o-inbox class="w-7 h-7"/></div>
                                    <p class="empty-title">Belum ada data penerimaan</p>
                                    <p class="empty-desc">Rekapitulasi target dan realisasi penerimaan akan tampil di sini setelah data tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-xs text-slate-500">Menampilkan {{ $penerimaans->count() }} laporan penerimaan</p>
            <p class="text-xs text-slate-400">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>
        </div>
    </x-card>
</x-app-layout>
