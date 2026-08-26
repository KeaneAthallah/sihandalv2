<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Sumber Dana" :breadcrumbs="['Data Master', 'Sumber Dana']">
            <x-slot name="actions">
                <a href="{{ route('sumber-dana.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Pagu" value="Rp {{ number_format($totalPagu / 1000000000, 1, ',', '.') }} M" color="primary"/>
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" color="success"/>
        <x-stat-card title="Persentase" value="{{ $persentase }}%" color="info"/>
        <x-stat-card title="Sumber Dana" value="{{ $sumberDanaTypes->count() }} Jenis" color="warning"/>
    </div>

    <x-card :padding="false">
        <div class="px-5 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="relative flex-1 max-w-sm">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                <input type="text" placeholder="Cari sumber dana..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"/>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-200">
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">No</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">Sumber Dana</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">OPD</th>
                        <th class="text-right px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">Pagu</th>
                        <th class="text-right px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">Realisasi</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide min-w-[140px]">Persentase</th>
                        <th class="text-center px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sumberDanaRecords as $idx => $src)
                        @php
                            $pers = $src->pagu > 0 ? round(($src->realisasi / $src->pagu) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 text-slate-400">{{ $sumberDanaRecords->firstItem() + $idx }}</td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-slate-800">{{ $src->nama_sumber_dana }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $src->opd?->nama ?? '-' }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap font-medium text-slate-700 tabular-nums">Rp {{ number_format($src->pagu, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap font-medium text-slate-700 tabular-nums">Rp {{ number_format($src->realisasi, 0, ',', '.') }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-slate-100 rounded-full h-1.5">
                                        <div class="bg-primary h-1.5 rounded-full transition-all" style="width: {{ max(0, min($pers, 100)) }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-slate-500 w-10 text-right tabular-nums">{{ $pers }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('sumber-dana.edit', $src) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form action="{{ route('sumber-dana.destroy', $src->id) }}" method="POST" class="inline" x-data @submit.prevent="if(confirm('Yakin ingin menghapus data ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400">
                                Belum ada data sumber dana.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sumberDanaRecords->hasPages())
            <div class="px-5 py-3 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-400">Menampilkan {{ $sumberDanaRecords->firstItem() }}–{{ $sumberDanaRecords->lastItem() }} dari {{ $sumberDanaRecords->total() }}</p>
                {{ $sumberDanaRecords->withQueryString()->links('pagination::tailwind') }}
            </div>
        @endif
    </x-card>
</x-app-layout>
