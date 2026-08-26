<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Organisasi Perangkat Daerah" :breadcrumbs="['Data Master', 'OPD']" />
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
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

    {{-- Data Table --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-100">
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
                            :class="active === '{{ $chip['key'] }}'
                                ? 'bg-primary text-white ring-2 ring-primary/20 shadow-sm'
                                : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300 hover:text-slate-800'"
                            class="px-3.5 py-1.5 text-sm font-medium rounded-lg transition-all duration-150">
                            {{ $chip['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="lg:ml-auto flex items-center gap-2">
                    <div class="relative flex-1 lg:flex-none">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                        <input type="text" placeholder="Cari nama / kode OPD..." class="w-full lg:w-72 pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="divide-y divide-slate-100">
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-12 text-left">No</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Nama OPD</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-32 text-left">Kode</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-24 text-center">Program</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-40 text-right">Total Pagu</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-20 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opds as $idx => $opd)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 text-slate-400">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3">
                                <span class="text-sm font-medium text-slate-800">{{ $opd->nama }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-500 font-mono text-sm">{{ $opd->kode }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-sm font-semibold text-slate-600">
                                    {{ $opd->programs_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <span class="text-sm font-semibold text-primary">
                                    Rp {{ number_format($opd->total_pagu / 1000000000, 1, ',', '.') }} M
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('opd.show', $opd) }}" title="Lihat Detail" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <p class="text-sm text-slate-500">Belum ada data OPD</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opds->count() > 0)
            <div class="px-5 py-3 border-t border-slate-100">
                <p class="text-sm text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $opds->count() }}</span> OPD</p>
            </div>
        @endif
    </x-card>
</x-app-layout>
