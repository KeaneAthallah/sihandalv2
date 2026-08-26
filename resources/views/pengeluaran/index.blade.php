<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengeluaran" :breadcrumbs="['Keuangan', 'Pengeluaran']">
            <x-slot name="actions">
                <a href="{{ route('pengeluaran.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Pengeluaran
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Pengeluaran" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="+8.2% dari bulan lalu" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Anggaran" value="Rp {{ number_format($totalAnggaran / 1000000000, 1, ',', '.') }} M" change="+3.1% dari bulan lalu" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-calendar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Persentase Realisasi" value="{{ $persentase }}%" change="Dari total anggaran" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Sisa Anggaran" value="Rp {{ number_format(($totalAnggaran - $totalRealisasi) / 1000000000, 1, ',', '.') }} M" change="Belum direalisasi" changeType="down" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-slate-100" x-data="{ active: 'all' }">
            @php
                $chips = [
                    ['key' => 'all', 'label' => 'Semua'],
                    ['key' => 'realisasi', 'label' => 'Realisasi'],
                    ['key' => 'pending', 'label' => 'Pending'],
                ];
            @endphp
            @foreach($chips as $chip)
                <button
                    @click="active = '{{ $chip['key'] }}'"
                    :class="active === '{{ $chip['key'] }}' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition">
                    {{ $chip['label'] }}
                </button>
            @endforeach

            <div class="ml-auto flex items-center gap-2">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"/>
                    <input type="text" placeholder="Cari SP2D, OPD..."
                        class="pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-700 w-48 lg:w-56 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"/>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[50px]">No</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[120px]">Tanggal</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">Kegiatan</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">OPD</th>
                        <th class="text-left px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[120px]">Sumber Dana</th>
                        <th class="text-right px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[130px]">Anggaran</th>
                        <th class="text-right px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[130px]">Realisasi</th>
                        <th class="text-center px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pengeluarans as $idx => $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-sm font-medium text-slate-800">{{ $item->nama_kegiatan ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-700 font-medium max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 whitespace-nowrap">
                                    {{ $item->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-5 py-3 font-medium text-slate-700 text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->anggaran / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-5 py-3 font-medium text-red-500 text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->realisasi / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('pengeluaran.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-md transition" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('pengeluaran.destroy', $item) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus pengeluaran ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-md transition">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <p class="text-sm text-slate-400">Belum ada data pengeluaran</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-sm text-slate-500">Menampilkan <span class="font-medium text-slate-700">{{ $pengeluarans->count() }}</span> data pengeluaran</p>
            @if(method_exists($pengeluarans, 'links'))
                <div class="text-sm">
                    {{ $pengeluarans->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>
</x-app-layout>
