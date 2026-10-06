<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Posisi Kas" :breadcrumbs="['Keuangan', 'Laporan Posisi Kas']">
            <x-slot name="actions">
                <a href="{{ route('laporan-posisi-kas.export') }}" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 inline mr-1"/>
                    Export CSV
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Report Header --}}
    <div class="bg-white rounded-xl border border-slate-200 mb-6 p-6">
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">Laporan Posisi Kas</h2>
            <p class="text-sm text-slate-500 mt-1">Rekapan saldo kas per OPD dan rekening</p>
        </div>
        <div class="flex items-center justify-center gap-6 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-calendar class="w-3.5 h-3.5"/>
                Periode: {{ now()->translatedFormat('F Y') }}
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-building-office-2 class="w-3.5 h-3.5"/>
                {{ $opdCount }} OPD
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-document-text class="w-3.5 h-3.5"/>
                {{ $totalCount }} Data
            </span>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-4 mb-6">
        <x-stat-card title="Total Saldo" value="Rp {{ number_format($totalSaldo / 1000000000, 1, ',', '.') }} M" change="saldo per {{ now()->translatedFormat('d M Y') }}" changeType="up" color="primary">
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

    {{-- Data Table --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="card-title">Detail Laporan Posisi Kas</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Rekapan saldo kas per OPD dan rekening</p>
                </div>
                <div class="text-xs text-slate-400 hidden sm:block">
                    Semua nilai dalam Miliar Rupiah (M)
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr>
                        <th class="px-5 py-3 table-head text-center w-10">No</th>
                        <th class="px-5 py-3 table-head text-left w-28">Tanggal</th>
                        <th class="px-5 py-3 table-head text-left">OPD</th>
                        <th class="px-5 py-3 table-head text-left w-40">Nama Rekening</th>
                        <th class="px-5 py-3 table-head text-left w-40">Nomor Rekening</th>
                        <th class="px-5 py-3 table-head text-right w-36">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($posisiKas as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $item->nama_rekening }}</td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->nomor_rekening ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-right font-bold text-slate-800 font-mono text-xs border-l-2 border-slate-200">
                                Rp {{ number_format($item->saldo / 1000000000, 1, ',', '.') }} M
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon"><x-heroicon-o-inbox class="w-7 h-7"/></div>
                                    <p class="empty-title">Belum ada data posisi kas</p>
                                    <p class="empty-desc">Rekapan saldo kas per OPD dan rekening akan tampil di sini setelah data tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-xs text-slate-500">Menampilkan <span class="font-medium text-slate-700">{{ $posisiKas->total() }}</span> data posisi kas</p>
            @if(method_exists($posisiKas, 'links'))
                <div class="text-sm">
                    {{ $posisiKas->withQueryString()->links() }}
                </div>
            @endif
            <p class="text-xs text-slate-400">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>
        </div>
    </x-card>
</x-app-layout>
