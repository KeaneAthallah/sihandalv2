<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $opd->nama }}" :breadcrumbs="['Data Master', 'OPD', 'Detail']" />
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Pagu" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Anggaran Kegiatan" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" color="info">
            <x-slot name="icon">
                <x-heroicon-o-chart-bar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Kegiatan" value="{{ $opd->kegiatans->count() }}" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-list-bullet class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Info & Sumber Dana Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-5">
        <x-card title="Informasi OPD">
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Kode OPD</span>
                    <span class="text-sm font-medium text-slate-800 font-mono">{{ $opd->kode }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Nama OPD</span>
                    <span class="text-sm font-medium text-slate-800 text-right max-w-[200px]">{{ $opd->nama }}</span>
                </div>
                @if($opd->kode_sub_unit)
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm text-slate-500">Kode Sub Unit</span>
                        <span class="text-sm font-medium text-slate-800 font-mono">{{ $opd->kode_sub_unit }}</span>
                    </div>
                @endif
                @if($opd->nama_sub_unit)
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm text-slate-500">Nama Sub Unit</span>
                        <span class="text-sm font-medium text-slate-800 text-right max-w-[200px]">{{ $opd->nama_sub_unit }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Total Pagu</span>
                    <span class="text-sm font-semibold text-primary">Rp {{ number_format($opd->total_pagu, 0, ',', '.') }}</span>
                </div>
            </div>
        </x-card>

        <x-card title="Ringkasan Sumber Dana">
            @php
                $sumberSummary = $opd->kegiatans
                    ->groupBy(fn ($k) => $k->sumberDana?->nama_sumber_dana ?? 'Tanpa Sumber')
                    ->map(function ($items) {
                        return (object) [
                            'nama' => $items->first()->sumberDana?->nama_sumber_dana ?? 'Tanpa Sumber',
                            'pagu' => $items->sum('pagu'),
                            'realisasi' => $items->sum('realisasi'),
                        ];
                    })
                    ->values();
            @endphp
            @if($sumberSummary->count() > 0)
                <div class="space-y-4">
                    @foreach($sumberSummary as $sd)
                        @php
                            $pers = $sd->pagu > 0 ? round(($sd->realisasi / $sd->pagu) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-slate-700">{{ $sd->nama }}</span>
                                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">Rp {{ number_format($sd->pagu / 1000000000, 1, ',', '.') }} M</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: {{ min($pers, 100) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-500 w-12 text-right">{{ $pers }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="py-8 text-center text-sm text-slate-500">Belum ada data sumber dana</p>
            @endif
        </x-card>
    </div>

    {{-- Kegiatan Table --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Program & Kegiatan</h3>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">{{ $opd->kegiatans->count() }} kegiatan</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="divide-y divide-slate-100">
                        <th class="px-5 py-3 table-head w-12 text-left">No</th>
                        <th class="px-5 py-3 table-head w-32 text-left">Kode</th>
                        <th class="px-5 py-3 table-head text-left">Program / Kegiatan</th>
                        <th class="px-5 py-3 table-head w-36 text-left">Sumber Dana</th>
                        <th class="px-5 py-3 table-head w-36 text-right">Pagu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opd->kegiatans as $idx => $kegiatan)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-mono font-semibold text-primary">{{ $kegiatan->program?->kode_program }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="text-xs font-semibold text-slate-500">{{ $kegiatan->program?->nama_program }}</p>
                                <p class="text-sm font-medium text-slate-800">{{ $kegiatan->nama_kegiatan }}</p>
                                @if($kegiatan->nama_sub_kegiatan)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $kegiatan->nama_sub_kegiatan }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-medium text-slate-600">{{ $kegiatan->sumberDana?->nama_sumber_dana ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <span class="text-sm font-semibold text-slate-700">
                                    Rp {{ number_format($kegiatan->pagu / 1000000000, 1, ',', '.') }} M
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-list-bullet class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada program & kegiatan</p>
                                    <p class="empty-desc">Program kegiatan OPD ini akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
