<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengeluaran" :breadcrumbs="['Keuangan', 'Pengeluaran']">
            <x-slot name="actions">
                <a href="{{ route('pengeluaran.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Pengeluaran
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Pengeluaran" value="Rp {{ number_format($totalJumlah / 1000000000, 1, ',', '.') }} M" change="Pengeluaran tercatat" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Data" value="{{ $pengeluarans->total() }}" change="Data pengeluaran" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-border-light" x-data="{ active: 'all' }">
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
                    :class="active === '{{ $chip['key'] }}' ? 'bg-primary text-white' : 'bg-surface-alt text-content-secondary hover:bg-border'"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition">
                    {{ $chip['label'] }}
                </button>
            @endforeach

            <div class="ml-auto flex items-center gap-2">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-content-muted absolute left-3 top-1/2 -translate-y-1/2"/>
                    <input type="text" placeholder="Cari SP2D, OPD..." class="input pl-9 w-48 lg:w-56"/>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-border-light">
                        <th class="text-left px-5 py-3 table-head w-[50px]">No</th>
                        <th class="text-left px-5 py-3 table-head w-[120px]">Tanggal</th>
                        <th class="text-left px-5 py-3 table-head">Kegiatan</th>
                        <th class="text-left px-5 py-3 table-head">OPD</th>
                        <th class="text-left px-5 py-3 table-head w-[120px]">Sumber Dana</th>
                        <th class="text-left px-5 py-3 table-head">Keperluan</th>
                        <th class="text-left px-5 py-3 table-head w-[140px]">No SP2D</th>
                        <th class="text-right px-5 py-3 table-head w-[130px]">Jumlah</th>
                        <th class="text-center px-5 py-3 table-head w-[100px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                    @forelse($pengeluarans as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-content-muted font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5 text-content-secondary whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm font-medium text-content">{{ $item->kegiatan?->nama_kegiatan ?? $item->nama_kegiatan ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-content-secondary font-medium max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-alt text-content-secondary whitespace-nowrap">
                                    {{ $item->sumberDana?->nama_sumber_dana ?? $item->sumber_dana ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-content-secondary max-w-[240px] truncate">{{ $item->keperluan ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-content-secondary whitespace-nowrap">{{ $item->no_sp2d ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-medium text-content-secondary text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('pengeluaran.edit', $item) }}" class="icon-btn hover:text-amber-600 hover:dark:text-amber-400 hover:bg-amber-50 hover:dark:bg-amber-500/10" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('pengeluaran.destroy', $item) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus pengeluaran ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="icon-btn hover:text-red-600 hover:dark:text-red-400 hover:bg-red-50 hover:dark:bg-red-500/10">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-arrow-up-right class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada data pengeluaran</p>
                                    <p class="empty-desc">Data pengeluaran dana akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-border-light flex items-center justify-between">
            <p class="text-sm text-content-muted">Menampilkan <span class="font-medium text-content-secondary">{{ $pengeluarans->total() }}</span> data pengeluaran</p>
            @if(method_exists($pengeluarans, 'links'))
                <div class="text-sm">
                    {{ $pengeluarans->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>
</x-app-layout>
