<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Posisi Kas" :breadcrumbs="['Keuangan', 'Posisi Kas']">
            <x-slot name="actions">
                <a href="{{ route('posisi-kas.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Posisi Kas
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Saldo Awal" value="Rp {{ number_format($totalSaldoAwal / 1000000000, 1, ',', '.') }} M" change="+3.2%" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-currency-dollar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Penerimaan" value="Rp {{ number_format($totalPenerimaan / 1000000000, 1, ',', '.') }} M" change="+12.5%" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Pengeluaran" value="Rp {{ number_format($totalPengeluaran / 1000000000, 1, ',', '.') }} M" change="+8.2%" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Saldo Akhir" value="Rp {{ number_format($totalSaldoAkhir / 1000000000, 1, ',', '.') }} M" change="-1.8%" changeType="down" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-currency-dollar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card>
        <x-slot name="header">
            <h3 class="text-sm font-semibold text-slate-800">Posisi Kas</h3>
            <p class="text-xs text-slate-400 mt-0.5">Detail posisi kas per rekening</p>
        </x-slot>

        <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
            <table class="w-full text-sm min-w-[1000px]">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                        <th class="px-4 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rekening</th>
                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Saldo Awal</th>
                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan</th>
                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengeluaran</th>
                        <th class="px-4 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Saldo Akhir</th>
                        <th class="px-4 py-3.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($posisiKas as $idx => $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-4 text-slate-500 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-4 py-4 text-slate-700 font-medium whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-4 text-slate-700 max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-4 py-4 text-slate-600 max-w-[220px] truncate">{{ $item->rekening->nama ?? '-' }}</td>
                            <td class="px-4 py-4 text-right text-slate-600 whitespace-nowrap">Rp {{ number_format($item->saldo_awal / 1000000000, 1, ',', '.') }} M</td>
                            <td class="px-4 py-4 text-right text-emerald-600 font-medium whitespace-nowrap">+ Rp {{ number_format($item->penerimaan / 1000000000, 1, ',', '.') }} M</td>
                            <td class="px-4 py-4 text-right text-red-600 font-medium whitespace-nowrap">- Rp {{ number_format($item->pengeluaran / 1000000000, 1, ',', '.') }} M</td>
                            <td class="px-4 py-4 text-right text-slate-800 font-bold whitespace-nowrap">Rp {{ number_format($item->saldo_akhir / 1000000000, 1, ',', '.') }} M</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('posisi-kas.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('posisi-kas.destroy', $item) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus data posisi kas ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    <p class="text-sm text-slate-500">Belum ada data posisi kas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $posisiKas->count() }} data</p>
        </div>
    </x-card>

</x-app-layout>
