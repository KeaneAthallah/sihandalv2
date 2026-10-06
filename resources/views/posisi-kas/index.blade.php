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

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4 mb-5">
        <x-stat-card title="Total Saldo" value="Rp {{ number_format($totalSaldo / 1000000000, 1, ',', '.') }} M" change="saldo per rekening" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-currency-dollar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Data" value="{{ $totalCount }}" change="total posisi kas" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-slate-100">
            <h3 class="card-title">Detail Posisi Kas</h3>
        </div>

        @if($posisiKas->total() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left px-5 py-3 table-head w-12">No</th>
                            <th class="text-left px-5 py-3 table-head w-[110px]">Tanggal</th>
                            <th class="text-left px-5 py-3 table-head">OPD</th>
                            <th class="text-left px-5 py-3 table-head">Nama Rekening</th>
                            <th class="text-left px-5 py-3 table-head w-[160px]">Nomor Rekening</th>
                            <th class="text-right px-5 py-3 table-head w-[160px]">Saldo</th>
                            <th class="text-center px-5 py-3 table-head w-[80px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($posisiKas as $idx => $item)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-slate-700 font-medium max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-slate-600 max-w-[220px] truncate">{{ $item->nama_rekening }}</td>
                                <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->nomor_rekening ?? '-' }}</td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-bold tabular-nums text-slate-800">Rp {{ number_format($item->saldo / 1000000000, 1, ',', '.') }} M</span>
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

            <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between">
                <p class="text-sm text-slate-500">Menampilkan <span class="font-medium text-slate-700">{{ $posisiKas->total() }}</span> data posisi kas</p>
                @if(method_exists($posisiKas, 'links'))
                    <div class="text-sm">
                        {{ $posisiKas->withQueryString()->links() }}
                    </div>
                @endif
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
