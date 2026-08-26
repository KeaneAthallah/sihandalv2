<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Transfer Dana" :breadcrumbs="['Transfer Dana']">
            <x-slot name="actions">
                <a href="{{ route('transfer-dana.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Transfer Baru
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
        <x-stat-card title="Total Transfer" value="Rp {{ number_format($totalTransfer / 1000000000, 1, ',', '.') }} M" change="+15.3%" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-arrows-right-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Transfer Berhasil" value="Rp {{ number_format($totalSelesai / 1000000000, 1, ',', '.') }} M" change="{{ $transferDanas->where('status', 'selesai')->count() }} transfer" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-check-circle class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Transfer Diproses" value="Rp {{ number_format($totalDiproses / 1000000000, 1, ',', '.') }} M" change="{{ $transferDanas->where('status', 'diproses')->count() }} transfer" changeType="up" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Transfer" value="{{ $transferDanas->count() }}" change="total" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
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
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">No Transfer</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                        <th class="text-left px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sumber Dana</th>
                        <th class="text-right px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="text-center px-4 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transferDanas as $idx => $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-4 text-slate-500">{{ $idx + 1 }}</td>
                            <td class="px-4 py-4 text-slate-700 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <span class="text-sm font-medium text-primary font-mono">{{ $item->nomor_transfer }}</span>
                            </td>
                            <td class="px-4 py-4 text-slate-700 max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200 whitespace-nowrap">
                                    {{ $item->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-800 text-right whitespace-nowrap">
                                Rp {{ number_format($item->jumlah / 1000000, 0, ',', '.') }}jt
                            </td>
                            <td class="px-4 py-4 text-center">
                                <x-status-badge :status="$item->status"/>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('transfer-dana.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-all" title="Edit">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('transfer-dana.destroy', $item) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus transfer ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <x-heroicon-o-inbox class="w-10 h-10 text-slate-300"/>
                                    <p class="text-sm text-slate-500">Belum ada data transfer</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $transferDanas->count() }} transfer</p>
        </div>
    </x-card>

</x-app-layout>
