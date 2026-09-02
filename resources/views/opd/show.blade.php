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
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-chart-bar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Program" value="{{ $programs->count() }}" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah UPT" value="{{ $upts->count() }}" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-building-office-2 class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Filter Bar --}}
    <x-filter-bar>
        <form method="GET" action="{{ route('opd.show', $opd) }}" class="flex items-center gap-3 flex-wrap w-full">
            @if(auth()->user()->isAdmin())
                <select name="nmskpd" class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <option value="">Semua NMSKPD</option>
                    @foreach(['4.01', '4.02', '4.03', '4.04', '4.05', '5.01'] as $n)
                        <option value="{{ $n }}" @selected(request('nmskpd') == $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <select name="dinas_id" class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <option value="">Semua Dinas</option>
                    @foreach($dinas as $d)
                        <option value="{{ $d->id }}" @selected(request('dinas_id') == $d->id)>{{ $d->nama }}</option>
                    @endforeach
                </select>
                <select name="unit_id" class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <option value="">Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" @selected(request('unit_id') == $u->id)>{{ $u->nama }}</option>
                    @endforeach
                </select>
            @endif
            <select name="program_id" class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                <option value="">Semua Program</option>
                @foreach($programs as $p)
                    <option value="{{ $p->id }}" @selected(request('program_id') == $p->id)>{{ $p->nama_program }}</option>
                @endforeach
            </select>
            <select name="sumber_dana_id" class="px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                <option value="">Semua Sumber Dana</option>
                @foreach($sumberDanas as $sd)
                    <option value="{{ $sd->id }}" @selected(request('sumber_dana_id') == $sd->id)>{{ $sd->nama_sumber_dana }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary">
                <x-heroicon-o-funnel class="w-4 h-4"/>
                Filter
            </button>
            @if(count($filters) > 0)
                <a href="{{ route('opd.show', $opd) }}" class="btn-secondary text-xs">Reset</a>
            @endif
        </form>
    </x-filter-bar>

    {{-- Hierarchy Structure --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-5">
        <x-card title="Struktur Organisasi">
            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">NMSKPD</span>
                    <span class="text-sm font-medium text-slate-800 font-mono">{{ $opd->nmskpd ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Dinas</span>
                    <span class="text-sm font-medium text-slate-800 text-right">{{ $opd->dinas?->nama ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Unit</span>
                    <span class="text-sm font-medium text-slate-800 text-right">{{ $opd->unit?->nama ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Kode OPD</span>
                    <span class="text-sm font-medium text-slate-800 font-mono">{{ $opd->kode }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Total Pagu</span>
                    <span class="text-sm font-semibold text-primary">Rp {{ number_format($totalPagu, 0, ',', '.') }}</span>
                </div>
            </div>
        </x-card>

        <x-card title="UPT Terkait">
            @forelse($upts as $upt)
                <div class="flex items-center justify-between py-2.5">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-building-office-2 class="w-4 h-4 text-slate-400"/>
                        <span class="text-sm font-medium text-slate-700">{{ $upt->nama }}</span>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">{{ $upt->kode ?? '-' }}</span>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-slate-500">Belum ada UPT</p>
            @endforelse
        </x-card>

        <x-card title="Ringkasan Sumber Dana">
            @php
                $sumberSummary = $programs
                    ->flatMap(fn ($p) => $p->kegiatans)
                    ->groupBy(fn ($k) => $k->subKegiatans->sum(fn ($s) => $s->belanjas->sum('pagu')));
                $sumberByNama = $programs
                    ->flatMap(fn ($p) => $p->kegiatans)
                    ->flatMap(fn ($k) => $k->subKegiatans)
                    ->flatMap(fn ($s) => $s->belanjas)
                    ->groupBy(fn ($b) => $b->sumberDana?->nama_sumber_dana ?? 'Tanpa Sumber')
                    ->map(function ($items) {
                        return (object) [
                            'nama' => $items->first()->sumberDana?->nama_sumber_dana ?? 'Tanpa Sumber',
                            'pagu' => $items->sum('pagu'),
                            'realisasi' => $items->sum('realisasi'),
                        ];
                    })
                    ->values();
            @endphp
            @if($sumberByNama->count() > 0)
                <div class="space-y-4">
                    @foreach($sumberByNama as $sd)
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

    {{-- Program & Kegiatan Hierarchy Table --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Program, Kegiatan, Sub Kegiatan & Belanja</h3>
                <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-lg">{{ $programs->count() }} program</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="divide-y divide-slate-100">
                        <th class="px-5 py-3 table-head w-10 text-left">No</th>
                        <th class="px-5 py-3 table-head text-left">Program / Kegiatan / Sub Kegiatan</th>
                        <th class="px-5 py-3 table-head w-32 text-left">Rekening</th>
                        <th class="px-5 py-3 table-head w-36 text-left">Sumber Dana</th>
                        <th class="px-5 py-3 table-head w-40 text-right">Pagu</th>
                        <th class="px-5 py-3 table-head w-40 text-right">Realisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($programs as $idx => $program)
                        @php
                            $programPagu = $program->kegiatans->sum(fn ($k) => $k->subKegiatans->sum(fn ($s) => $s->belanjas->sum('pagu')));
                            $programRealisasi = $program->kegiatans->sum(fn ($k) => $k->subKegiatans->sum(fn ($s) => $s->belanjas->sum('realisasi')));
                        @endphp
                        <tr class="table-row bg-slate-50/70">
                            <td class="px-5 py-3.5 text-slate-400">{{ $idx + 1 }}</td>
                            <td colspan="5" class="px-5 py-3.5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-mono font-semibold text-primary">{{ $program->kode_program }}</span>
                                        <span class="text-sm font-semibold text-slate-800 ml-2">{{ $program->nama_program }}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500">Rp {{ number_format($programPagu / 1000000000, 1, ',', '.') }} M</span>
                                </div>
                            </td>
                        </tr>
                        @foreach($program->kegiatans as $kegiatan)
                            @php
                                $kegiatanPagu = $kegiatan->subKegiatans->sum(fn ($s) => $s->belanjas->sum('pagu'));
                                $kegiatanRealisasi = $kegiatan->subKegiatans->sum(fn ($s) => $s->belanjas->sum('realisasi'));
                            @endphp
                            <tr class="table-row bg-white">
                                <td></td>
                                <td colspan="5" class="px-5 py-3">
                                    <div class="flex items-center justify-between pl-5">
                                        <div>
                                            <span class="text-xs font-mono text-slate-500">{{ $kegiatan->kode_kegiatan }}</span>
                                            <span class="text-sm font-medium text-slate-800 ml-2">{{ $kegiatan->nama_kegiatan }}</span>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-500">Rp {{ number_format($kegiatanPagu / 1000000000, 1, ',', '.') }} M</span>
                                    </div>
                                </td>
                            </tr>
                            @forelse($kegiatan->subKegiatans as $sub)
                                <tr class="table-row">
                                    <td></td>
                                    <td class="px-5 py-2.5 pl-14">
                                        <span class="text-xs font-mono text-slate-400">{{ $sub->kode_sub_kegiatan }}</span>
                                        <span class="text-sm text-slate-600 ml-2">{{ $sub->nama_sub_kegiatan }}</span>
                                    </td>
                                    <td class="px-5 py-2.5">
                                        <span class="text-xs text-slate-400">{{ $sub->belanjas->first()?->rekening?->nama ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-2.5">
                                        <span class="text-xs text-slate-400">{{ $sub->belanjas->first()?->sumberDana?->nama_sumber_dana ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-2.5 text-right">
                                        <span class="text-xs font-medium text-slate-500">Rp {{ number_format($sub->belanjas->sum('pagu'), 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-5 py-2.5 text-right">
                                        <span class="text-xs font-medium text-slate-500">Rp {{ number_format($sub->belanjas->sum('realisasi'), 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-row">
                                    <td colspan="6" class="px-5 py-2.5 pl-16 text-xs text-slate-400">Tidak ada sub kegiatan</td>
                                </tr>
                            @endforelse
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-clipboard-document-list class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada program</p>
                                    <p class="empty-desc">Program & kegiatan OPD ini akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>
