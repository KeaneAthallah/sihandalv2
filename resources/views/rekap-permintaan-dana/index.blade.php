<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Rekap Permintaan Dana" :breadcrumbs="['Rekap Permintaan Dana']">
            <x-slot name="actions">
                <a href="{{ route('rekap-permintaan-dana.export') }}" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 inline mr-1"/>
                    Export CSV
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Report Header --}}
    <div class="bg-white rounded-xl border border-slate-200 mb-6 p-6">
        <div class="text-center mb-4">
            <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">Rekapitulasi Permintaan Dana</h2>
            <p class="text-sm text-slate-500 mt-1">Ringkasan seluruh permintaan dana berdasarkan status</p>
        </div>
        <div class="flex items-center justify-center gap-6 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-calendar class="w-3.5 h-3.5"/>
                Periode: {{ now()->translatedFormat('F Y') }}
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-building-office-2 class="w-3.5 h-3.5"/>
                {{ $permintaanDanas->pluck('opd_id')->unique()->count() }} OPD
            </span>
            <span class="flex items-center gap-1.5">
                <x-heroicon-o-document-text class="w-3.5 h-3.5"/>
                {{ $permintaanDanas->count() }} Total Permintaan
            </span>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card title="Total Permintaan" value="{{ $permintaanDanas->count() }}" change="Semua permintaan" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Disetujui" value="{{ $permintaanDanas->where('status', 'disetujui')->count() }}" change="Rp {{ number_format($totalDisetujui / 1000000000, 1, ',', '.') }} M" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-check-circle class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Ditolak" value="{{ $permintaanDanas->where('status', 'ditolak')->count() }}" change="Rp {{ number_format($totalDitolak / 1000000000, 1, ',', '.') }} M" changeType="down" color="danger">
            <x-slot name="icon">
                <x-heroicon-o-x-circle class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Menunggu" value="{{ $permintaanDanas->where('status', 'menunggu')->count() }}" change="Rp {{ number_format($totalMenunggu / 1000000000, 1, ',', '.') }} M" changeType="up" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Summary Breakdown --}}
    @php
        $total = $permintaanDanas->count() ?: 1;
        $statusCounts = [
            'disetujui' => $permintaanDanas->where('status', 'disetujui')->count(),
            'ditolak' => $permintaanDanas->where('status', 'ditolak')->count(),
            'menunggu' => $permintaanDanas->where('status', 'menunggu')->count(),
            'draft' => $permintaanDanas->where('status', 'draft')->count(),
        ];
        $statusColors = [
            'disetujui' => 'bg-emerald-500',
            'ditolak' => 'bg-red-500',
            'menunggu' => 'bg-amber-500',
            'draft' => 'bg-slate-400',
        ];
        $statusLabels = [
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'menunggu' => 'Menunggu',
            'draft' => 'Draft',
        ];
    @endphp
    <div class="bg-white rounded-xl border border-slate-200 mb-6 p-5">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-4">Distribusi Status Permintaan</h3>
        <div class="w-full bg-slate-100 rounded-full h-3 flex overflow-hidden mb-4">
            @foreach($statusCounts as $status => $count)
                @if($count > 0)
                    <div class="{{ $statusColors[$status] }} h-3 transition-all duration-500" style="width: {{ ($count / $total) * 100 }}%" title="{{ $statusLabels[$status] }}: {{ $count }}"></div>
                @endif
            @endforeach
        </div>
        <div class="flex flex-wrap items-center gap-4 text-xs">
            @foreach($statusCounts as $status => $count)
                @if($count > 0)
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $statusColors[$status] }}"></span>
                        <span class="text-slate-600 font-medium">{{ $statusLabels[$status] }}</span>
                        <span class="text-slate-800 font-bold">{{ $count }}</span>
                        <span class="text-slate-400">({{ round(($count / $total) * 100, 1) }}%)</span>
                    </span>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Data Table --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="card-title">Daftar Permintaan Dana</h3>
            <p class="text-xs text-slate-400 mt-0.5">Detail seluruh permintaan dana yang tercatat</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr>
                        <th class="px-5 py-3 table-head text-center w-10">No</th>
                        <th class="px-5 py-3 table-head text-left">No. Permintaan</th>
                        <th class="px-5 py-3 table-head text-left w-28">Tanggal</th>
                        <th class="px-5 py-3 table-head text-left">OPD</th>
                        <th class="px-5 py-3 table-head text-left w-36">Sumber Dana</th>
                        <th class="px-5 py-3 table-head text-right w-40">Nilai (Rp)</th>
                        <th class="px-5 py-3 table-head text-center w-32">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permintaanDanas as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $item->nomor_permintaan }}</td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-800">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $item->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-slate-800 font-mono text-xs">
                                Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <x-status-badge :status="$item->status"/>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon"><x-heroicon-o-inbox class="w-7 h-7"/></div>
                                    <p class="empty-title">Belum ada data permintaan dana</p>
                                    <p class="empty-desc">Ringkasan seluruh permintaan dana akan tampil di sini setelah data tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-xs text-slate-500">Menampilkan {{ $permintaanDanas->count() }} data permintaan dana</p>
            <p class="text-xs text-slate-400">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>
        </div>
    </x-card>
</x-app-layout>
