<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $opd->nama }}" :breadcrumbs="['OPD', 'Detail']" />
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <x-stat-card title="Total Pagu" value="Rp {{ number_format($opd->total_pagu / 1000000000, 1, ',', '.') }} M" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Sumber Dana" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" color="info">
            <x-slot name="icon">
                <x-heroicon-o-chart-bar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Program" value="{{ $opd->programs->count() }}" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-list-bullet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-slate-800 mb-4">Informasi OPD</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Kode OPD</span>
                    <span class="text-sm font-medium text-slate-800 font-mono">{{ $opd->kode }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Nama OPD</span>
                    <span class="text-sm font-medium text-slate-800">{{ $opd->nama }}</span>
                </div>
                @if($opd->kode_sub_unit)
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Kode Sub Unit</span>
                    <span class="text-sm font-medium text-slate-800 font-mono">{{ $opd->kode_sub_unit }}</span>
                </div>
                @endif
                @if($opd->nama_sub_unit)
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Nama Sub Unit</span>
                    <span class="text-sm font-medium text-slate-800">{{ $opd->nama_sub_unit }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-sm text-slate-500">Total Pagu</span>
                    <span class="text-sm font-semibold text-primary">Rp {{ number_format($opd->total_pagu, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-slate-800 mb-4">Ringkasan Sumber Dana</h3>
            @if($opd->sumberDanas->count() > 0)
                <div class="space-y-3">
                    @foreach($opd->sumberDanas as $sd)
                        @php
                            $pers = $sd->pagu > 0 ? round(($sd->realisasi / $sd->pagu) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-sm text-slate-700">{{ $sd->nama_sumber_dana }}</span>
                                <span class="text-xs font-semibold text-slate-500">Rp {{ number_format($sd->pagu / 1000000000, 1, ',', '.') }} M</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all" style="width: {{ min($pers, 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-400">Belum ada data sumber dana</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-800">Program & Kegiatan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kegiatan</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sumber Dana</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pagu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($opd->programs as $idx => $prog)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-mono font-semibold text-primary bg-blue-50 px-2.5 py-1 rounded-lg">{{ $prog->kode_kegiatan }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-slate-800">{{ $prog->nama_kegiatan }}</span>
                                @if($prog->nama_sub_kegiatan)
                                    <br><span class="text-xs text-slate-400">{{ $prog->nama_sub_kegiatan }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200">
                                    {{ $prog->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-700 text-right">
                                Rp {{ number_format($prog->pagu / 1000000000, 1, ',', '.') }} M
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-400">
                                Belum ada program & kegiatan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
