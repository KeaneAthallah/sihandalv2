<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $subKegiatan->kode_sub_kegiatan }} — {{ $subKegiatan->nama_sub_kegiatan }}" :breadcrumbs="['Program & Kegiatan', $kegiatan->nama_kegiatan, 'Sub Kegiatan', 'Belanja']">
            <x-slot name="actions">
                <a href="{{ route('belanja.create', $subKegiatan) }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Belanja
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    @php
        $totalCommit = (float) $belanjas->sum('dana_di_commit');
        $sisaPagu = (float) $totalPagu - $totalCommit - (float) $totalRealisasi;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Pagu" value="Rp {{ number_format((float) $totalPagu, 0, ',', '.') }}" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format((float) $totalRealisasi, 0, ',', '.') }}" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-trending-up class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Dana Tercommit" value="Rp {{ number_format($totalCommit, 0, ',', '.') }}" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-lock-closed class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Sisa Pagu" value="Rp {{ number_format($sisaPagu, 0, ',', '.') }}" color="{{ $sisaPagu < 0 ? 'danger' : 'info' }}">
            <x-slot name="icon">
                <x-heroicon-o-chart-bar class="w-5 h-5"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="px-5 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                <input type="text" placeholder="Cari belanja..." class="input pl-9"/>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 table-head">No</th>
                        <th class="text-left px-5 py-3 table-head">Rekening</th>
                        <th class="text-left px-5 py-3 table-head">Sumber Dana</th>
                        <th class="text-right px-5 py-3 table-head">Pagu</th>
                        <th class="text-right px-5 py-3 table-head">Realisasi</th>
                        <th class="text-right px-5 py-3 table-head">Committed</th>
                        <th class="text-center px-5 py-3 table-head">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($belanjas as $belanja)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-mono font-semibold text-primary whitespace-nowrap">{{ $belanja->rekening?->kode }}</span>
                                <p class="text-sm font-medium text-slate-800">{{ $belanja->rekening?->nama ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-slate-600">{{ $belanja->sumberDana?->nama_sumber_dana ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-semibold text-slate-700 whitespace-nowrap">Rp {{ number_format((float) $belanja->pagu, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-medium text-emerald-600 whitespace-nowrap">Rp {{ number_format((float) $belanja->realisasi, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-medium text-amber-600 whitespace-nowrap">Rp {{ number_format((float) $belanja->dana_di_commit, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('belanja.edit', ['subKegiatan' => $subKegiatan, 'belanja' => $belanja]) }}" class="icon-btn hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form action="{{ route('belanja.destroy', ['subKegiatan' => $subKegiatan, 'belanja' => $belanja->id]) }}" method="POST" class="inline" x-data @submit.prevent="if(confirm('Yakin ingin menghapus belanja ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn hover:text-red-600 hover:bg-red-50" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-banknotes class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data belanja</p>
                                    <p class="empty-desc">Data belanja akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-app-layout>