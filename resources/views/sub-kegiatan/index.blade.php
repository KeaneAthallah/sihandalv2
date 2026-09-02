<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $kegiatan->kode_kegiatan }} — {{ $kegiatan->nama_kegiatan }}" :breadcrumbs="['Program & Kegiatan', 'Kegiatan', 'Sub Kegiatan']">
            <x-slot name="actions">
                <a href="{{ route('sub-kegiatan.create', $kegiatan) }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Sub Kegiatan
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    @php
        $totalPagu = (float) $subKegiatans->sum('pagu');
        $totalRealisasi = (float) $subKegiatans->sum('realisasi');
        $persentase = $totalPagu > 0 ? round($totalRealisasi / $totalPagu * 100, 2) : 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Pagu" value="Rp {{ number_format($totalPagu, 0, ',', '.') }}" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi, 0, ',', '.') }}" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-trending-up class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase" value="{{ number_format($persentase, 2, ',', '.') }}%" change="{{ count($subKegiatans) }} sub kegiatan" color="info">
            <x-slot name="icon">
                <x-heroicon-o-chart-pie class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="px-5 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                <input type="text" placeholder="Cari sub kegiatan..." class="input pl-9"/>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 table-head">No</th>
                        <th class="text-left px-5 py-3 table-head">Kode Sub Kegiatan</th>
                        <th class="text-left px-5 py-3 table-head">Nama Sub Kegiatan</th>
                        <th class="text-right px-5 py-3 table-head">Pagu</th>
                        <th class="text-right px-5 py-3 table-head">Realisasi</th>
                        <th class="text-center px-5 py-3 table-head">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subKegiatans as $sub)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-mono font-semibold text-primary whitespace-nowrap">{{ $sub->kode_sub_kegiatan }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-slate-800">{{ $sub->nama_sub_kegiatan }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-semibold text-slate-700 whitespace-nowrap">Rp {{ number_format((float) $sub->pagu, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-medium text-slate-600 whitespace-nowrap">Rp {{ number_format((float) $sub->realisasi, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('belanja.index', $sub) }}" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-primary/10 text-primary hover:bg-primary/20 transition whitespace-nowrap">
                                        Belanja
                                    </a>
                                    <a href="{{ route('sub-kegiatan.edit', ['kegiatan' => $kegiatan, 'subKegiatan' => $sub]) }}" class="icon-btn hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-clipboard-document-list class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data sub kegiatan</p>
                                    <p class="empty-desc">Data sub kegiatan akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>