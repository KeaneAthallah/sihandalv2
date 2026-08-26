<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Penerimaan" :breadcrumbs="['Laporan', 'Penerimaan']" />
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <x-stat-card title="Total Penerimaan" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="+12.5%" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Target" value="Rp {{ number_format($totalTarget / 1000000000, 1, ',', '.') }} M" change="+5.2%" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-calculator class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase Tercapai" value="{{ $persentase }}%" change="dari target" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-arrow-trending-up class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Data" value="{{ $penerimaans->count() }}" change="total records" changeType="up" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-800">Detail Laporan Penerimaan</h3>
            <p class="text-xs text-slate-400 mt-0.5">Rekapitulasi target dan realisasi penerimaan</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sumber Dana</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Target</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Realisasi</th>
                        <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Persentase</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($penerimaans as $idx => $item)
                        @php
                            $selisih = $item->realisasi - $item->target;
                            $barColor = match(true) {
                                $item->persentase >= 100 => 'bg-emerald-500',
                                $item->persentase >= 80 => 'bg-blue-500',
                                $item->persentase >= 60 => 'bg-amber-500',
                                default => 'bg-red-500',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700">
                                    {{ $item->nama_sumber_dana ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600 text-right font-medium">
                                Rp {{ number_format($item->target / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-800 text-right">
                                Rp {{ number_format($item->realisasi / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col items-center gap-1.5">
                                    <div class="w-full bg-slate-100 rounded-full h-2 max-w-[120px]">
                                        <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ min($item->persentase, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500">{{ $item->persentase }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-right {{ $selisih >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $selisih >= 0 ? '+' : '' }}Rp {{ number_format($selisih / 1000000000, 1, ',', '.') }} M
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $penerimaans->count() }} laporan</p>
        </div>
    </div>
</x-app-layout>
