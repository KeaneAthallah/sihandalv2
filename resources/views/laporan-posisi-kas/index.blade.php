<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Laporan Posisi Kas" :breadcrumbs="['Keuangan', 'Laporan Posisi Kas']">
            <x-slot name="actions">
                <a href="{{ route('laporan-posisi-kas.export') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-all shadow-sm">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                    Export CSV
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Report Header --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-6 p-6 print:shadow-none print:border-none">
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">Laporan Posisi Kas</h2>
            <p class="text-sm text-slate-500 mt-1">Rekapan posisi kas per OPD dan rekening</p>
        </div>
        <div class="flex items-center justify-center gap-6 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-calendar class="w-3.5 h-3.5"/>
                Periode: {{ now()->translatedFormat('F Y') }}
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-building-office-2 class="w-3.5 h-3.5"/>
                {{ $posisiKas->pluck('opd_id')->unique()->count() }} OPD
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-document-text class="w-3.5 h-3.5"/>
                {{ $posisiKas->count() }} Data
            </span>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
        <x-stat-card title="Saldo Akhir" value="Rp {{ number_format($totalSaldoAkhir / 1000000000, 1, ',', '.') }} M" change="Saldo per {{ now()->translatedFormat('d M Y') }}" changeType="{{ $totalSaldoAkhir >= $totalSaldoAwal ? 'up' : 'down' }}" color="{{ $totalSaldoAkhir >= $totalSaldoAwal ? 'success' : 'warning' }}">
            <x-slot name="icon">
                <x-heroicon-o-currency-dollar class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Penerimaan" value="Rp {{ number_format($totalPenerimaan / 1000000000, 1, ',', '.') }} M" change="Kumulatif masuk" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Pengeluaran" value="Rp {{ number_format($totalPengeluaran / 1000000000, 1, ',', '.') }} M" change="Kumulatif keluar" changeType="up" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-arrow-up-right class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Saldo Awal" value="Rp {{ number_format($totalSaldoAwal / 1000000000, 1, ',', '.') }} M" change="Saldo periode awal" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden print:shadow-none print:border print:border-slate-300">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Detail Laporan Posisi Kas</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Rekapan posisi kas per OPD dan rekening</p>
                </div>
                <div class="text-xs text-slate-400 hidden sm:block">
                    Semua nilai dalam Miliar Rupiah (M)
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-slate-200 bg-slate-50/80">
                        <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider w-10">No</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-28">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">OPD</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider w-40">Rekening</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider w-32">Saldo Awal</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider w-32">Penerimaan</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider w-32">Pengeluaran</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-600 uppercase tracking-wider w-36">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($posisiKas as $idx => $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3.5 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-medium text-slate-800">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $item->rekening->nama ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-right text-slate-600 font-mono text-xs">
                                Rp {{ number_format($item->saldo_awal / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-4 py-3.5 text-right text-emerald-600 font-semibold font-mono text-xs">
                                + Rp {{ number_format($item->penerimaan / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-4 py-3.5 text-right text-red-500 font-semibold font-mono text-xs">
                                - Rp {{ number_format($item->pengeluaran / 1000000000, 1, ',', '.') }} M
                            </td>
                            <td class="px-4 py-3.5 text-right font-bold text-slate-800 font-mono text-xs border-l-2 border-slate-200">
                                Rp {{ number_format($item->saldo_akhir / 1000000000, 1, ',', '.') }} M
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    <p class="text-sm text-slate-400">Belum ada data posisi kas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3.5 border-t border-slate-200 bg-slate-50/50 flex items-center justify-between">
            <p class="text-xs text-slate-500">Menampilkan {{ $posisiKas->count() }} data posisi kas</p>
            <p class="text-xs text-slate-400">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>
        </div>
    </div>
</x-app-layout>
