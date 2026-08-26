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
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-800">Antrian Permintaan Dana</h3>
            <p class="text-xs text-slate-400 mt-0.5">{{ $totalMenunggu }} permintaan menunggu keputusan Anda</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[1100px]">
                <thead>
                    <tr>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">No</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Pengaju</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">OPD</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-right">Nilai</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Sumber Dana</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Keperluan</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-left">Tanggal</th>
                        <th class="px-5 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($permintaanDanas as $idx => $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3 text-slate-400 text-xs font-medium">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($item->opd->nama ?? 'O', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $item->nomor_permintaan }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ $item->catatan ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-sm text-slate-700 max-w-[180px] truncate block">{{ $item->opd->nama ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <span class="text-sm font-bold text-slate-900 whitespace-nowrap">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200 whitespace-nowrap">
                                    {{ $item->sumber_dana }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="text-sm text-slate-600 max-w-[220px] truncate block" title="{{ $item->keperluan }}">
                                    {{ Str::limit($item->keperluan, 40) ?: '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                <span class="text-xs text-slate-500">{{ $item->tanggal?->format('d M Y') ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('permintaan-dana.edit', $item) }}" class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition" title="Lihat Detail">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </a>
                                    <button
                                        type="button"
                                        x-data="{
                                            item: {
                                                id: {{ $item->id }},
                                                nomor: '{{ $item->nomor_permintaan }}',
                                                jumlah: '{{ number_format($item->jumlah, 0, ',', '.') }}',
                                                opd: '{{ $item->opd->nama ?? '-' }}',
                                                sumberDana: '{{ $item->sumber_dana }}',
                                                keperluan: '{{ addslashes(Str::limit($item->keperluan, 60)) }}'
                                            }
                                        }"
                                        @click="$dispatch('open-modal', 'approve-confirm'); window.selectedItem = $data.item"
                                        class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                                        title="Setujui"
                                    >
                                        <x-heroicon-o-check class="w-4 h-4"/>
                                    </button>
                                    <button
                                        type="button"
                                        x-data="{
                                            item: {
                                                id: {{ $item->id }},
                                                nomor: '{{ $item->nomor_permintaan }}',
                                                jumlah: '{{ number_format($item->jumlah, 0, ',', '.') }}',
                                                opd: '{{ $item->opd->nama ?? '-' }}',
                                                sumberDana: '{{ $item->sumber_dana }}',
                                                keperluan: '{{ addslashes(Str::limit($item->keperluan, 60)) }}'
                                            }
                                        }"
                                        @click="$dispatch('open-modal', 'reject-confirm'); window.selectedItem = $data.item"
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
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
                                    <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-500">
                                        <x-heroicon-o-check-badge class="w-10 h-10"/>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-700">Semua permintaan telah ditindaklanjuti</p>
                                        <p class="text-xs text-slate-400 mt-1">Tidak ada permintaan dana yang menunggu persetujuan saat ini</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100">
            <p class="text-sm text-slate-500">Menampilkan {{ $permintaanDanas->count() }} permintaan menunggu persetujuan</p>
        </div>
    </x-card>

    {{-- Modal Konfirmasi Setuju --}}
    <x-modal name="approve-confirm" max-width="md">
        <div class="p-6" x-data="{ get item() { return window.selectedItem || {} } }">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600">
                    <x-heroicon-o-check-circle class="w-6 h-6"/>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Setujui Permintaan Dana</h3>
                    <p class="text-sm text-slate-500">Konfirmasi persetujuan dana</p>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 space-y-2.5 mb-5">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Nomor</span>
                    <span class="font-semibold text-slate-800" x-text="item.nomor">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">OPD</span>
                    <span class="font-semibold text-slate-800" x-text="item.opd">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Keperluan</span>
                    <span class="font-medium text-slate-700 text-right max-w-[250px]" x-text="item.keperluan">-</span>
                </div>
                <div class="border-t border-slate-200 pt-2.5 mt-2.5">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-slate-600">Nilai yang Disetujui</span>
                        <span class="text-lg font-bold text-emerald-700" x-text="'Rp ' + item.jumlah">-</span>
                    </div>
                </div>
            </div>

            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 mb-5">
                <div class="flex items-start gap-2.5">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"/>
                    <p class="text-sm text-emerald-700 leading-relaxed">
                        Dengan menyetujui, dana sebesar <span class="font-bold" x-text="'Rp ' + item.jumlah"></span> akan direalisasikan dari sumber dana <span class="font-semibold" x-text="item.sumberDana"></span>.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'approve-confirm')" class="px-4 py-2 bg-white border border-slate-300 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-50 transition">
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
        <div class="p-6" x-data="{ get item() { return window.selectedItem || {} } }">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 rounded-xl bg-red-50 text-red-600">
                    <x-heroicon-o-x-circle class="w-6 h-6"/>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Tolak Permintaan Dana</h3>
                    <p class="text-sm text-slate-500">Konfirmasi penolakan dana</p>
                </div>
            </div>

            <div class="bg-slate-50 rounded-xl p-4 space-y-2.5 mb-5">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Nomor</span>
                    <span class="font-semibold text-slate-800" x-text="item.nomor">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">OPD</span>
                    <span class="font-semibold text-slate-800" x-text="item.opd">-</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Keperluan</span>
                    <span class="font-medium text-slate-700 text-right max-w-[250px]" x-text="item.keperluan">-</span>
                </div>
                <div class="border-t border-slate-200 pt-2.5 mt-2.5">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-slate-600">Nilai yang Ditolak</span>
                        <span class="text-lg font-bold text-red-700" x-text="'Rp ' + item.jumlah">-</span>
                    </div>
                </div>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-xl p-3.5 mb-5">
                <div class="flex items-start gap-2.5">
                    <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 shrink-0 mt-0.5"/>
                    <p class="text-sm text-red-700 leading-relaxed">
                        Dengan menolak, komitmen dana sebesar <span class="font-bold" x-text="'Rp ' + item.jumlah"></span> dari sumber dana <span class="font-semibold" x-text="item.sumberDana"></span> akan dilepaskan kembali.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal', 'reject-confirm')" class="px-4 py-2 bg-white border border-slate-300 rounded-lg font-medium text-sm text-slate-700 hover:bg-slate-50 transition">
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
