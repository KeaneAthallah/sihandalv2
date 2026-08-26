<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Rekap Permintaan Dana" :breadcrumbs="['Rekap Permintaan Dana']" />
    </x-slot>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stat-card title="Total Permintaan" value="{{ $permintaanDanas->count() }}" change="+5 baru" changeType="up" color="primary">
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

    {{-- Data Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Daftar Permintaan Dana</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-6 py-3 text-left font-semibold text-slate-600">No</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600">No Permintaan</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600">Tanggal</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600">OPD</th>
                        <th class="px-6 py-3 text-left font-semibold text-slate-600">Sumber Dana</th>
                        <th class="px-6 py-3 text-right font-semibold text-slate-600">Nilai</th>
                        <th class="px-6 py-3 text-center font-semibold text-slate-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permintaanDanas as $idx => $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-slate-800">{{ $item->nomor_permintaan }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $item->sumber_dana }}</td>
                            <td class="px-6 py-4 text-right font-medium text-slate-800">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <x-status-badge :status="$item->status"/>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-sm text-slate-400">
                                Belum ada data permintaan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-6 py-4">
            <p class="text-sm text-slate-500">Menampilkan {{ $permintaanDanas->count() }} data</p>
        </div>
    </div>
</x-app-layout>
