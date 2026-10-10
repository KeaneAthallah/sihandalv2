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
        <x-stat-card title="Total Kegiatan" value="{{ $opds->sum('kegiatans_count') }}" color="success">
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
        <div class="px-5 py-4 border-b border-border-light">
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
                                : 'bg-card text-content-secondary border border-border hover:border-border-strong hover:text-content'"
                            class="px-3.5 py-1.5 text-sm font-medium rounded-lg transition-all duration-150">
                            {{ $chip['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="lg:ml-auto flex items-center gap-2">
                    <div class="relative flex-1 lg:flex-none">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-1/2 -translate-y-1/2"/>
                        <input type="text" placeholder="Cari nama / kode OPD..." class="input pl-9 lg:!w-72"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="divide-y divide-border-light">
                        <th class="px-5 py-3 table-head w-12 text-left">No</th>
                        <th class="px-5 py-3 table-head text-left">Nama OPD</th>
                        <th class="px-5 py-3 table-head w-32 text-left">Kode</th>
                        <th class="px-5 py-3 table-head w-24 text-center">Kegiatan</th>
                        <th class="px-5 py-3 table-head w-40 text-right">Total Pagu</th>
                        <th class="px-5 py-3 table-head w-20 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                    @forelse($opds as $idx => $opd)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-content-muted">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm font-medium text-content">{{ $opd->nama }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-content-muted font-mono text-sm">{{ $opd->kode }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-alt text-sm font-semibold text-content-secondary">
                                    {{ $opd->kegiatans_count }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <span class="text-sm font-semibold text-primary">
                                    Rp {{ number_format($opd->total_pagu / 1000000000, 1, ',', '.') }} M
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('opd.show', $opd) }}" title="Lihat Detail" class="icon-btn hover:text-amber-600 hover:dark:text-amber-400 hover:bg-amber-50 hover:dark:bg-amber-500/10">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-building-office-2 class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data OPD</p>
                                    <p class="empty-desc">Data organisasi perangkat daerah akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opds->total() > 0)
            <div class="px-5 py-3 border-t border-border-light flex items-center justify-between">
                <p class="text-sm text-content-muted">Menampilkan <span class="font-semibold text-content-secondary">{{ $opds->total() }}</span> OPD</p>
                <div class="text-sm">
                    {{ $opds->withQueryString()->links() }}
                </div>
            </div>
        @endif
    </x-card>
</x-app-layout>
