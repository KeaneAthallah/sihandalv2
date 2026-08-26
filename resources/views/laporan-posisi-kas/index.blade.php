<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Posisi Kas" :breadcrumbs="['Keuangan', 'Laporan Posisi Kas']" />
    </x-slot>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-6">
        <x-stat-card title="Saldo Akhir" value="Rp {{ number_format($totalSaldoAkhir / 1000000000, 1, ',', '.') }} M" change="-1.8%" changeType="down" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-currency-dollar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Penerimaan Kumulatif" value="Rp {{ number_format($totalPenerimaan / 1000000000, 1, ',', '.') }} M" change="+12.5%" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Pengeluaran Kumulatif" value="Rp {{ number_format($totalPengeluaran / 1000000000, 1, ',', '.') }} M" change="+8.2%" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Saldo Awal" value="Rp {{ number_format($totalSaldoAwal / 1000000000, 1, ',', '.') }} M" change="+4.3%" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-800">Detail Laporan Posisi Kas</h3>
            <p class="text-xs text-slate-400 mt-0.5">Rekapan posisi kas</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                        <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rekening</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Saldo Awal</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Penerimaan</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengeluaran</th>
                        <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($posisiKas as $idx => $item)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-slate-500 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4 text-slate-700 font-medium">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->rekening->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">Rp {{ number_format($item->saldo_awal / 1000000000, 1, ',', '.') }} M</td>
                            <td class="px-6 py-4 text-right text-emerald-600 font-medium">+ Rp {{ number_format($item->penerimaan / 1000000000, 1, ',', '.') }} M</td>
                            <td class="px-6 py-4 text-right text-red-600 font-medium">- Rp {{ number_format($item->pengeluaran / 1000000000, 1, ',', '.') }} M</td>
                            <td class="px-6 py-4 text-right text-slate-800 font-bold">Rp {{ number_format($item->saldo_akhir / 1000000000, 1, ',', '.') }} M</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-sm text-slate-400">
                                Belum ada data posisi kas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $posisiKas->count() }} data</p>
        </div>
    </div>
</x-app-layout>
