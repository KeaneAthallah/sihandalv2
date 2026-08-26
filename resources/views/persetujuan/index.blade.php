<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Persetujuan" :breadcrumbs="['Persetujuan']" />
    </x-slot>

    {{-- Flash Messages --}}
    @if (session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="danger" :dismissible="true">{{ session('error') }}</x-alert>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Menunggu Persetujuan" value="{{ $totalMenunggu }}" change="perlu ditindaklanjuti" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Nilai Menunggu" value="Rp {{ number_format($totalMenungguNilai / 1000000000, 1, ',', '.') }} M" change="total outstanding" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Data Table --}}
    <x-card>
        <div class="overflow-x-auto -mx-5 lg:-mx-6 px-5 lg:px-6">
            <table class="w-full text-sm min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50/70">
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No Permintaan</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sumber Dana</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permintaanDanas as $idx => $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-4 py-4 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <span class="text-sm font-medium text-primary font-mono">{{ $item->nomor_permintaan }}</span>
                            </td>
                            <td class="px-4 py-4 text-slate-700 max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200 whitespace-nowrap">
                                    {{ $item->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-800 text-right whitespace-nowrap">
                                Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('permintaan-dana.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all" title="Lihat Detail">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('persetujuan.setujui', $item) }}" class="inline" @submit.prevent="if(confirm('Setujui permintaan {{ $item->nomor_permintaan }}?')) $el.submit()">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-all" title="Setujui">
                                            <x-heroicon-o-check class="w-4 h-4"/>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('persetujuan.tolak', $item) }}" class="inline" @submit.prevent="if(confirm('Tolak permintaan {{ $item->nomor_permintaan }}?')) $el.submit()">
                                        @csrf
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Tolak">
                                            <x-heroicon-o-x-mark class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    <p class="text-sm text-slate-500">Tidak ada permintaan yang menunggu persetujuan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $permintaanDanas->count() }} permintaan menunggu persetujuan</p>
        </div>
    </x-card>
</x-app-layout>
