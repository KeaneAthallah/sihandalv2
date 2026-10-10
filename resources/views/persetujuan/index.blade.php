<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Persetujuan" :breadcrumbs="['Persetujuan']" />
    </x-slot>

    @if (session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif
    @if (session('error'))
        <x-alert type="danger" :dismissible="true">{{ session('error') }}</x-alert>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5 mb-6">
        <x-stat-card title="Menunggu Persetujuan" value="{{ $totalMenunggu }}" change="perlu ditindaklanjuti" color="warning">
            <x-slot name="icon">
                <x-heroicon-o-clock class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Total Nilai Menunggu" value="Rp {{ number_format($totalMenungguNilai, 0, ',', '.') }}" change="total outstanding" color="primary">
            <x-slot name="icon">
                <x-heroicon-o-banknotes class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Rata-rata per Permintaan" value="{{ $totalMenunggu > 0 ? 'Rp ' . number_format($totalMenungguNilai / $totalMenunggu, 0, ',', '.') : 'Rp 0' }}" change="per permintaan" color="info">
            <x-slot name="icon">
                <x-heroicon-o-calculator class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Antrian Persetujuan --}}
    <x-card :padding="false">
        <div class="px-5 py-4 border-b border-border-light">
            <h3 class="card-title">Antrian Permintaan Dana</h3>
            <p class="text-xs text-content-muted mt-0.5">{{ $totalMenunggu }} permintaan menunggu keputusan Anda</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[1100px]">
                <thead>
                    <tr>
                        <th class="px-5 py-3 table-head text-left">No</th>
                        <th class="px-5 py-3 table-head text-left">Pengaju</th>
                        <th class="px-5 py-3 table-head text-left">OPD</th>
                        <th class="px-5 py-3 table-head text-right">Nilai</th>
                        <th class="px-5 py-3 table-head text-left">Sumber Dana</th>
                        <th class="px-5 py-3 table-head text-left">Keperluan</th>
                        <th class="px-5 py-3 table-head text-left">Tanggal</th>
                        <th class="px-5 py-3 table-head text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light">
                    @forelse($permintaanDanas as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-content-muted text-xs font-medium">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($item->opd->nama ?? 'O', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-content truncate">{{ $item->nomor_permintaan }}</p>
                                        <p class="text-xs text-content-muted truncate">{{ $item->catatan ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-content-secondary max-w-[180px] truncate block">{{ $item->opd->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="text-sm font-bold text-content whitespace-nowrap">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface text-content-secondary border border-border whitespace-nowrap">
                                    {{ $item->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-sm text-content-secondary max-w-[220px] truncate block" title="{{ $item->keperluan }}">
                                    {{ Str::limit($item->keperluan, 40) ?: '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="text-xs text-content-muted">{{ $item->tanggal?->format('d M Y') ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('permintaan-dana.edit', $item) }}" class="icon-btn hover:text-primary hover:bg-blue-50 hover:dark:bg-blue-500/10" title="Lihat Detail">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </a>
                                    <button
                                        type="button"
                                        x-data="{
                                            item: {
                                                id: @js($item->id),
                                                nomor: @js($item->nomor_permintaan),
                                                jumlah: @js(number_format($item->jumlah, 0, ',', '.')),
                                                opd: @js($item->opd->nama ?? '-'),
                                                sumberDana: @js($item->sumber_dana),
                                                keperluan: @js(Str::limit($item->keperluan, 60))
                                            }
                                        }"
                                        @click="$dispatch('open-modal', 'approve-confirm'); $dispatch('approve-item', $data.item)"
                                        class="icon-btn hover:text-emerald-600 hover:dark:text-emerald-400 hover:bg-emerald-50 hover:dark:bg-emerald-500/10"
                                        title="Setujui"
                                    >
                                        <x-heroicon-o-check class="w-4 h-4"/>
                                    </button>
                                    <button
                                        type="button"
                                        x-data="{
                                            item: {
                                                id: @js($item->id),
                                                nomor: @js($item->nomor_permintaan),
                                                jumlah: @js(number_format($item->jumlah, 0, ',', '.')),
                                                opd: @js($item->opd->nama ?? '-'),
                                                sumberDana: @js($item->sumber_dana),
                                                keperluan: @js(Str::limit($item->keperluan, 60))
                                            }
                                        }"
                                        @click="$dispatch('open-modal', 'reject-confirm'); $dispatch('reject-item', $data.item)"
                                        class="icon-btn hover:text-red-600 hover:dark:text-red-400 hover:bg-red-50 hover:dark:bg-red-500/10"
                                        title="Tolak"
                                    >
                                        <x-heroicon-o-x-mark class="w-4 h-4"/>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500">
                                        <x-heroicon-o-check-badge class="w-10 h-10"/>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-content-secondary">Semua permintaan telah ditindaklanjuti</p>
                                        <p class="text-xs text-content-muted mt-1">Tidak ada permintaan dana yang menunggu persetujuan saat ini</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-border-light flex items-center justify-between">
            <p class="text-sm text-content-muted">Menampilkan <span class="font-medium text-content-secondary">{{ $permintaanDanas->total() }}</span> permintaan menunggu persetujuan</p>
            @if(method_exists($permintaanDanas, 'links'))
                <div class="text-sm">
                    {{ $permintaanDanas->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>

    {{-- Modal Konfirmasi Setuju --}}
    <x-modal name="approve-confirm" max-width="md">
        <div class="p-6" x-data="{ item: {} }" x-on:approve-item.window="item = $event.detail">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <x-heroicon-o-check-circle class="w-6 h-6"/>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-content">Setujui Permintaan Dana</h3>
                    <p class="text-sm text-content-muted">Konfirmasi persetujuan dana</p>
                </div>
            </div>

            <div class="bg-surface rounded-xl p-4 space-y-2.5 mb-5">
                <div class="flex justify-between text-sm">
                    <span class="text-content-muted">Nomor</span>
                    <span class="font-semibold text-content" x-text="item.nomor">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-content-muted">OPD</span>
                    <span class="font-semibold text-content" x-text="item.opd">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-content-muted">Keperluan</span>
                    <span class="font-medium text-content-secondary text-right max-w-[250px]" x-text="item.keperluan">-</span>
                </div>
                <div class="border-t border-border pt-2.5 mt-2.5">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-content-secondary">Nilai yang Disetujui</span>
                        <span class="text-lg font-bold text-emerald-700 dark:text-emerald-400" x-text="'Rp ' + item.jumlah">-</span>
                    </div>
                </div>
            </div>

            <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl p-3.5 mb-5">
                <div class="flex items-start gap-2.5">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5"/>
                    <p class="text-sm text-emerald-700 dark:text-emerald-400 leading-relaxed">
                        Dengan menyetujui, permintaan sebesar <span class="font-bold" x-text="'Rp ' + item.jumlah"></span> dari sumber dana <span class="font-semibold" x-text="item.sumberDana"></span> akan berstatus disetujui.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'approve-confirm')" class="btn-secondary">
                    Batal
                </button>
                <form method="POST" :action="'/persetujuan/' + item.id + '/setujui'" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition">
                        <x-heroicon-s-check class="w-4 h-4"/>
                        Ya, Setujui
                    </button>
                </form>
            </div>
        </div>
    </x-modal>

    {{-- Modal Konfirmasi Tolak --}}
    <x-modal name="reject-confirm" max-width="md">
        <div class="p-6" x-data="{ item: {} }" x-on:reject-item.window="item = $event.detail">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 rounded-xl bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
                    <x-heroicon-o-x-circle class="w-6 h-6"/>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-content">Tolak Permintaan Dana</h3>
                    <p class="text-sm text-content-muted">Konfirmasi penolakan dana</p>
                </div>
            </div>

            <div class="bg-surface rounded-xl p-4 space-y-2.5 mb-5">
                <div class="flex justify-between text-sm">
                    <span class="text-content-muted">Nomor</span>
                    <span class="font-semibold text-content" x-text="item.nomor">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-content-muted">OPD</span>
                    <span class="font-semibold text-content" x-text="item.opd">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-content-muted">Keperluan</span>
                    <span class="font-medium text-content-secondary text-right max-w-[250px]" x-text="item.keperluan">-</span>
                </div>
                <div class="border-t border-border pt-2.5 mt-2.5">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-content-secondary">Nilai yang Ditolak</span>
                        <span class="text-lg font-bold text-red-700 dark:text-red-400" x-text="'Rp ' + item.jumlah">-</span>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl p-3.5 mb-5">
                <div class="flex items-start gap-2.5">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5"/>
                    <p class="text-sm text-red-700 dark:text-red-400 leading-relaxed">
                        Dengan menolak, permintaan sebesar <span class="font-bold" x-text="'Rp ' + item.jumlah"></span> dari sumber dana <span class="font-semibold" x-text="item.sumberDana"></span> akan berstatus ditolak.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'reject-confirm')" class="btn-secondary">
                    Batal
                </button>
                <form method="POST" :action="'/persetujuan/' + item.id + '/tolak'" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition">
                        <x-heroicon-s-x-mark class="w-4 h-4"/>
                        Ya, Tolak
                    </button>
                </form>
            </div>
        </div>
    </x-modal>
</x-app-layout>
