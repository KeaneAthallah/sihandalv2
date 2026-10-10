<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Permintaan Dana OPD" :breadcrumbs="['Keuangan', 'Permintaan Dana OPD']">
            <x-slot name="actions">
                <a href="{{ route('permintaan-dana.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Buat Permintaan
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif

    @if($errors->any())
        <x-alert type="danger" :dismissible="true">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
        <x-stat-card title="Total Permintaan" value="Rp {{ number_format($totalPermintaan / 1000000000, 1, ',', '.') }} M" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Disetujui" value="Rp {{ number_format($totalDisetujui / 1000000000, 1, ',', '.') }} M" color="success">
            <x-slot name="icon">
                <x-heroicon-o-check-circle class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Menunggu Persetujuan" value="Rp {{ number_format($totalMenunggu / 1000000000, 1, ',', '.') }} M" change="{{ $totalMenungguCount }} permintaan" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Permintaan" value="{{ $totalPermintaanCount }}" change="semua status" color="info">
            <x-slot name="icon">
                <x-heroicon-o-clipboard-document-list class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-border-light">
            <h3 class="card-title">Daftar Permintaan Dana</h3>
            <div class="ml-auto relative w-full sm:w-72">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-content-muted pointer-events-none"/>
                <input type="text" placeholder="Cari nomor, OPD, keperluan..."
                    class="input pl-9"/>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[800px]">
                <thead>
                    <tr class="border-b border-border-light">
                        <th class="text-left px-5 py-3 table-head w-12">No</th>
                        <th class="text-left px-5 py-3 table-head">Nomor</th>
                        <th class="text-left px-5 py-3 table-head">OPD</th>
                        <th class="text-left px-5 py-3 table-head">Sumber Dana</th>
                        <th class="text-left px-5 py-3 table-head">Keperluan</th>
                        <th class="text-right px-5 py-3 table-head">Jumlah</th>
                        <th class="text-center px-5 py-3 table-head">Status</th>
                        <th class="text-center px-5 py-3 table-head">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                    @forelse($permintaanDanas as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-content-muted font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-sm font-mono font-semibold text-primary">{{ $item->nomor_permintaan }}</span>
                                    <span class="text-xs text-content-muted">{{ $item->tanggal ? $item->tanggal->format('d M Y') : '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-content-secondary max-w-[180px] truncate block" title="{{ $item->opd->nama ?? '-' }}">{{ $item->opd->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface text-content-secondary border border-border whitespace-nowrap">
                                    {{ $item->sumberDana?->nama_sumber_dana ?? $item->sumber_dana ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-content-secondary max-w-[220px] truncate block" title="{{ $item->keperluan }}">{{ $item->keperluan }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-medium tabular-nums text-content-secondary whitespace-nowrap">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <x-status-badge :status="$item->status"/>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        x-data
                                        @click="
                                            $dispatch('open-view-permintaan', {
                                                nomor: @js($item->nomor_permintaan),
                                                opd: @js($item->opd->nama ?? '-'),
                                                sumber_dana: @js($item->sumber_dana),
                                                kegiatan: @js($item->kegiatan->nama_kegiatan ?? ($item->kegiatan ? $item->kegiatan->kode_kegiatan : '-')),
                                                keperluan: @js($item->keperluan),
                                                jumlah: @js(number_format($item->jumlah, 0, ',', '.')),
                                                status: @js($item->status),
                                                tanggal: @js($item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d M Y') : '-'),
                                                created: @js($item->created_at->format('d M Y H:i'))
                                            });
                                            $dispatch('open-modal', 'view-permintaan-dana');
                                        "
                                        title="Lihat Detail"
                                        class="icon-btn hover:text-primary hover:bg-primary/10">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </button>

                                    @if(in_array($item->status, ['draft', 'ditolak']))
                                        <a href="{{ route('permintaan-dana.edit', $item) }}" title="Edit"
                                           class="icon-btn hover:text-amber-600 hover:dark:text-amber-400 hover:bg-amber-50 hover:dark:bg-amber-500/10">
                                            <x-heroicon-o-pencil class="w-4 h-4"/>
                                        </a>
                                    @endif

                                    @if($item->status === 'draft')
                                        <form method="POST" action="{{ route('permintaan-dana.submit', $item) }}"
                                              x-data
                                              @submit.prevent="if(confirm('Ajukan permintaan ini? Dana akan di-commit dari pagu sumber dana.')) $el.submit()">
                                            @csrf
                                            <button type="submit" title="Ajukan"
                                                    class="icon-btn hover:text-blue-600 hover:dark:text-blue-400 hover:bg-blue-50 hover:dark:bg-blue-500/10">
                                                <x-heroicon-o-paper-airplane class="w-4 h-4"/>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($item->status, ['draft', 'ditolak']))
                                        <form method="POST" action="{{ route('permintaan-dana.destroy', $item) }}"
                                              x-data
                                              @submit.prevent="if(confirm('Hapus permintaan ini? Tindakan ini tidak dapat dibatalkan.')) $el.submit()">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus"
                                                    class="icon-btn hover:text-red-600 hover:dark:text-red-400 hover:bg-red-50 hover:dark:bg-red-500/10">
                                                <x-heroicon-o-trash class="w-4 h-4"/>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-document-text class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada permintaan dana</p>
                                    <p class="empty-desc">Buat permintaan dana pertama untuk mulai mengelola keuangan OPD Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-border-light flex items-center justify-between">
            <p class="text-sm text-content-muted">Menampilkan <span class="font-medium text-content-secondary">{{ $permintaanDanas->total() }}</span> permintaan dana</p>
            @if(method_exists($permintaanDanas, 'links'))
                <div class="text-sm">
                    {{ $permintaanDanas->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>

    <x-modal name="view-permintaan-dana" maxWidth="2xl">
        <div class="px-6 py-5 border-b border-border-light">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-content">Detail Permintaan Dana</h3>
                    <p class="text-sm text-content-muted mt-0.5" x-data x-on:open-view-permintaan.window="$el.textContent = 'Nomor: ' + ($event.detail.nomor || '')" x-init="$el.textContent = ''"></p>
                </div>
                <button @click="$dispatch('close-modal', 'view-permintaan-dana')" class="p-2 text-content-muted hover:text-content-secondary hover:bg-surface-alt rounded-lg transition">
                    <x-heroicon-o-x-mark class="w-5 h-5"/>
                </button>
            </div>
        </div>

        <div class="px-6 py-5" x-data="{ viewData: {} }" x-on:open-view-permintaan.window="viewData = $event.detail">
            <div class="space-y-5">
                <div class="flex items-center gap-3 p-4 rounded-xl"
                     :class="{
                         'bg-surface border border-border': viewData.status === 'draft',
                         'bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30': viewData.status === 'menunggu',
                         'bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30': viewData.status === 'disetujui',
                         'bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30': viewData.status === 'ditolak',
                     }">
                    <div class="p-2 rounded-lg"
                         :class="{
                             'bg-border text-content-secondary': viewData.status === 'draft',
                             'bg-amber-200 text-amber-700 dark:text-amber-400': viewData.status === 'menunggu',
                             'bg-emerald-200 text-emerald-700 dark:text-emerald-400': viewData.status === 'disetujui',
                             'bg-red-200 text-red-700 dark:text-red-400': viewData.status === 'ditolak',
                         }">
                        <x-heroicon-o-document-text class="w-5 h-5"/>
                    </div>
                    <div>
                        <p class="text-sm font-semibold" :class="{
                            'text-content-secondary': viewData.status === 'draft',
                            'text-amber-700 dark:text-amber-400': viewData.status === 'menunggu',
                            'text-emerald-700 dark:text-emerald-400': viewData.status === 'disetujui',
                            'text-red-700 dark:text-red-400': viewData.status === 'ditolak',
                        }" x-text="viewData.status === 'draft' ? 'Draft' : viewData.status === 'menunggu' ? 'Menunggu Persetujuan' : viewData.status === 'disetujui' ? 'Disetujui' : 'Ditolak'"></p>
                        <p class="text-xs opacity-70" :class="{
                            'text-content-secondary': viewData.status === 'draft',
                            'text-amber-600 dark:text-amber-400': viewData.status === 'menunggu',
                            'text-emerald-600 dark:text-emerald-400': viewData.status === 'disetujui',
                            'text-red-600 dark:text-red-400': viewData.status === 'ditolak',
                        }">Status permintaan saat ini</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-content-muted uppercase tracking-wider mb-1">Nomor Permintaan</p>
                        <p class="text-sm font-mono font-semibold text-content" x-text="viewData.nomor"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-content-muted uppercase tracking-wider mb-1">Tanggal</p>
                        <p class="text-sm text-content-secondary" x-text="viewData.tanggal"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-content-muted uppercase tracking-wider mb-1">OPD</p>
                        <p class="text-sm text-content-secondary" x-text="viewData.opd"></p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-content-muted uppercase tracking-wider mb-1">Sumber Dana</p>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-alt text-content-secondary border border-border" x-text="viewData.sumber_dana"></span>
                    </div>
                </div>

                <div class="border-t border-border-light pt-4">
                    <p class="text-xs font-medium text-content-muted uppercase tracking-wider mb-1">Keperluan</p>
                    <p class="text-sm text-content-secondary" x-text="viewData.keperluan"></p>
                </div>

                @if(isset($item) && $item->kegiatan)
                <div x-show="viewData.kegiatan && viewData.kegiatan !== '-'">
                    <p class="text-xs font-medium text-content-muted uppercase tracking-wider mb-1">Kegiatan</p>
                    <p class="text-sm text-content-secondary" x-text="viewData.kegiatan"></p>
                </div>
                @endif

                <div class="p-4 bg-surface rounded-xl border border-border">
                    <p class="text-xs font-medium text-content-muted uppercase tracking-wider mb-1">Jumlah Permintaan</p>
                    <p class="text-2xl font-bold text-content" x-text="'Rp ' + viewData.jumlah"></p>
                </div>

                <div class="text-xs text-content-muted" x-text="'Dibuat: ' + viewData.created"></div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-border-light flex justify-end">
            <x-secondary-button @click="$dispatch('close-modal', 'view-permintaan-dana')">Tutup</x-secondary-button>
        </div>
    </x-modal>

</x-app-layout>
