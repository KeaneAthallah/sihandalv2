<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $opd->nama }}" :breadcrumbs="['OPD', 'Detail']" />
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
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

    {{-- Info & Sumber Dana Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Info Panel --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-lg bg-primary/10">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-primary"/>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Informasi OPD</h3>
                </div>
            </x-slot>

            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Kode OPD</span>
                    <span class="text-sm font-medium text-slate-800 font-mono bg-slate-100 px-2.5 py-0.5 rounded">{{ $opd->kode }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Nama OPD</span>
                    <span class="text-sm font-medium text-slate-800 text-right max-w-[200px]">{{ $opd->nama }}</span>
                </div>
                @if($opd->kode_sub_unit)
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm text-slate-500">Kode Sub Unit</span>
                        <span class="text-sm font-medium text-slate-800 font-mono bg-slate-100 px-2.5 py-0.5 rounded">{{ $opd->kode_sub_unit }}</span>
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

        {{-- Sumber Dana Summary --}}
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-lg bg-emerald-50">
                        <x-heroicon-o-currency-dollar class="w-4 h-4 text-emerald-600"/>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Ringkasan Sumber Dana</h3>
                </div>
            </x-slot>

            @if($opd->sumberDanas->count() > 0)
                <div class="space-y-4">
                    @foreach($opd->sumberDanas as $sd)
                        @php
                            $pers = $sd->pagu > 0 ? round(($sd->realisasi / $sd->pagu) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-slate-700">{{ $sd->nama_sumber_dana }}</span>
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
                <div class="py-8 text-center">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                        <x-heroicon-o-document-text class="w-6 h-6 text-slate-300"/>
                    </div>
                    <p class="text-sm text-slate-500">Belum ada data sumber dana</p>
                </div>
            @endif
        </x-card>
    </div>

    {{-- Programs Table --}}
    <x-card>
        <x-slot name="header">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <div class="p-2 rounded-lg bg-blue-50">
                        <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-blue-600"/>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">Program & Kegiatan</h3>
                </div>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">{{ $opd->programs->count() }} program</span>
            </div>
        </x-slot>

        <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
            <table class="w-full text-sm min-w-[650px]">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-12">No</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-32">Kode</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kegiatan</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Sumber Dana</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Pagu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opd->programs as $idx => $prog)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3.5 text-slate-400 text-sm">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-mono font-semibold text-primary bg-primary/10">
                                    {{ $prog->kode_kegiatan }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="text-sm font-medium text-slate-800">{{ $prog->nama_kegiatan }}</p>
                                @if($prog->nama_sub_kegiatan)
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $prog->nama_sub_kegiatan }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium text-slate-600 bg-slate-100">
                                    {{ $prog->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <span class="text-sm font-semibold text-slate-700">
                                    Rp {{ number_format($prog->pagu / 1000000000, 1, ',', '.') }} M
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <x-heroicon-o-clipboard-document-list class="w-7 h-7 text-slate-300"/>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">Belum ada program & kegiatan</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Program akan muncul setelah ditambahkan</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
