<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Organisasi Perangkat Daerah" :breadcrumbs="['OPD']" />
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Total OPD" value="{{ $totalOpd }} OPD" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-building-office-2 class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Program" value="{{ $opds->sum('programs_count') }}" color="success">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Penerimaan" value="{{ $opds->sum('penerimaans_count') }}" color="info">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Anggaran" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Filter Bar --}}
    <x-card class="mb-6" x-data="{ active: 'all' }">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                @php
                    $chips = [
                        ['key' => 'all', 'label' => 'Semua'],
                        ['key' => 'aktif', 'label' => 'Aktif'],
                        ['key' => 'belum-aktif', 'label' => 'Belum Aktif'],
                        ['key' => 'dinas', 'label' => 'Dinas'],
                        ['key' => 'badan', 'label' => 'Badan'],
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
            </div>

            <div class="lg:ml-auto flex items-center gap-2">
                <div class="relative flex-1 lg:flex-none">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                    <input type="text" placeholder="Cari nama / kode OPD..." class="w-full lg:w-72 pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all"/>
                </div>
                <button class="px-4 py-2 bg-slate-100 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-200 transition-all whitespace-nowrap">
                    <x-heroicon-o-funnel class="w-4 h-4 inline mr-1"/>
                    Filter
                </button>
            </div>
        </div>
    </x-card>

    {{-- Data Table --}}
    <x-card>
        <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nama OPD</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Kode OPD</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Program</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Pagu</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opds as $idx => $opd)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 shrink-0 rounded-xl bg-primary/10 flex items-center justify-center">
                                        <x-heroicon-o-building-office-2 class="w-4 h-4 text-primary"/>
                                    </div>
                                    <span class="text-sm font-medium text-slate-800">{{ $opd->nama }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4 text-slate-500 font-mono">{{ $opd->kode }}</td>
                            <td class="px-4 py-4 text-slate-600">{{ $opd->programs_count }}</td>
                            <td class="px-4 py-4 font-semibold text-primary text-right whitespace-nowrap">
                                Rp {{ number_format($opd->total_pagu / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('opd.show', $opd) }}" title="Lihat Detail" class="p-1.5 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    <p class="text-sm text-slate-500">Belum ada data OPD</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $opds->count() }} OPD</p>
        </div>
    </x-card>
</x-app-layout>
