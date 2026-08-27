<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Posisi Kas" :breadcrumbs="['Keuangan', 'Posisi Kas']">
            <x-slot name="actions">
                <a href="{{ route('posisi-kas.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Tambah Posisi Kas
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
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

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-slate-100">
            <h3 class="card-title">Detail Posisi Kas</h3>
        </div>

        @if($posisiKas->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left px-5 py-3 table-head w-12">No</th>
                            <th class="text-left px-5 py-3 table-head w-[110px]">Tanggal</th>
                            <th class="text-left px-5 py-3 table-head">OPD</th>
                            <th class="text-left px-5 py-3 table-head">Rekening</th>
                            <th class="text-right px-5 py-3 table-head w-[130px]">Saldo Awal</th>
                            <th class="text-right px-5 py-3 table-head w-[130px]">Penerimaan</th>
                            <th class="text-right px-5 py-3 table-head w-[130px]">Pengeluaran</th>
                            <th class="text-right px-5 py-3 table-head w-[130px]">Saldo Akhir</th>
                            <th class="text-center px-5 py-3 table-head w-[80px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($posisiKas as $idx => $item)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-slate-700 font-medium max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-slate-600 max-w-[220px] truncate">{{ $item->rekening->nama ?? '-' }}</td>
                                <td class="px-5 py-3.5 font-medium tabular-nums text-slate-700 text-right whitespace-nowrap">Rp {{ number_format($item->saldo_awal / 1000000000, 1, ',', '.') }} M</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-medium tabular-nums text-emerald-600">+ Rp {{ number_format($item->penerimaan / 1000000000, 1, ',', '.') }} M</span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-medium tabular-nums text-red-500">- Rp {{ number_format($item->pengeluaran / 1000000000, 1, ',', '.') }} M</span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-bold tabular-nums text-slate-800">Rp {{ number_format($item->saldo_akhir / 1000000000, 1, ',', '.') }} M</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('posisi-kas.edit', $item) }}" class="icon-btn hover:text-amber-600 hover:bg-amber-50" title="Edit">
                                            <x-heroicon-o-pencil class="w-4 h-4"/>
                                        </a>
                                        <form method="POST" action="{{ route('posisi-kas.destroy', $item) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus data posisi kas ini?')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="icon-btn hover:text-red-600 hover:bg-red-50">
                                                <x-heroicon-o-trash class="w-4 h-4"/>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-slate-100">
                <p class="text-sm text-slate-500">Menampilkan {{ $posisiKas->count() }} data posisi kas</p>
            </div>
        @else
            <div class="px-5 py-14 text-center">
                <div class="inline-flex flex-col items-center">
                    <div class="empty-icon">
                        <x-heroicon-o-currency-dollar class="w-7 h-7"/>
                    </div>
                    <p class="empty-title">Belum ada data posisi kas</p>
                    <p class="empty-desc">Data posisi kas akan tampil di sini.</p>
                </div>
            </div>
        @endif
    </x-card>

</x-app-layout>
