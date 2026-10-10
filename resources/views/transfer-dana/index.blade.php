<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Transfer Dana" :breadcrumbs="['Transfer Dana']">
            <x-slot name="actions">
                <a href="{{ route('transfer-dana.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Transfer Baru
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Transfer" value="Rp {{ number_format($totalTransfer / 1000000000, 1, ',', '.') }} M" change="+15.3%" changeType="up" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-arrows-right-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Transfer Berhasil" value="Rp {{ number_format($totalSelesai / 1000000000, 1, ',', '.') }} M" change="{{ $totalSelesaiCount }} transfer" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-check-circle class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Transfer Diproses" value="Rp {{ number_format($totalDiproses / 1000000000, 1, ',', '.') }} M" change="{{ $totalDiprosesCount }} transfer" changeType="up" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Transfer" value="{{ $transferDanas->total() }}" change="total" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-border-light">
            <h3 class="card-title">Daftar Transfer Dana</h3>
        </div>

        @if($transferDanas->total() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead>
                        <tr class="border-b border-border-light">
                            <th class="text-left px-5 py-3 table-head w-12">No</th>
                            <th class="text-left px-5 py-3 table-head w-[110px]">Tanggal</th>
                            <th class="text-left px-5 py-3 table-head w-[140px]">No Transfer</th>
                            <th class="text-left px-5 py-3 table-head">OPD</th>
                            <th class="text-left px-5 py-3 table-head w-[240px]">Sumber Dana (Pengirim &rarr; Penerima)</th>
                            <th class="text-right px-5 py-3 table-head w-[130px]">Nilai</th>
                            <th class="text-center px-5 py-3 table-head w-[110px]">Status</th>
                            <th class="text-center px-5 py-3 table-head w-[80px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light">
                        @foreach($transferDanas as $idx => $item)
                            <tr class="table-row">
                                <td class="px-5 py-3.5 text-content-muted font-medium tabular-nums">{{ $idx + 1 }}</td>
                                <td class="px-5 py-3.5 text-content-secondary whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="font-mono text-sm font-semibold text-primary">{{ $item->nomor_transfer }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-content-secondary max-w-[200px] truncate">{{ $item->opd->nama ?? '-' }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-surface-alt text-content-secondary border border-border/60 whitespace-nowrap">
                                        {{ $item->sumberDanaPengirim->nama_sumber_dana ?? '-' }} &rarr; {{ $item->sumberDanaPenerima->nama_sumber_dana ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-medium tabular-nums text-content-secondary">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <x-status-badge :status="$item->status"/>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('transfer-dana.edit', $item) }}" class="icon-btn hover:text-amber-600 hover:dark:text-amber-400 hover:bg-amber-50 hover:dark:bg-amber-500/10" title="Edit">
                                            <x-heroicon-o-pencil class="w-4 h-4"/>
                                        </a>
                                        <form method="POST" action="{{ route('transfer-dana.destroy', $item) }}" x-data @submit.prevent="if(confirm('Yakin ingin menghapus transfer ini?')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="icon-btn hover:text-red-600 hover:dark:text-red-400 hover:bg-red-50 hover:dark:bg-red-500/10">
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

            <div class="px-5 py-3 border-t border-border-light flex items-center justify-between">
                <p class="text-sm text-content-muted">Menampilkan <span class="font-medium text-content-secondary">{{ $transferDanas->total() }}</span> transfer dana</p>
                @if(method_exists($transferDanas, 'links'))
                    <div class="text-sm">
                        {{ $transferDanas->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="px-5 py-14 text-center">
                <div class="inline-flex flex-col items-center">
                    <div class="empty-icon">
                        <x-heroicon-o-arrows-right-left class="w-7 h-7"/>
                    </div>
                    <p class="empty-title">Belum ada data transfer</p>
                    <p class="empty-desc">Data transfer dana akan tampil di sini.</p>
                </div>
            </div>
        @endif
    </x-card>

</x-app-layout>
