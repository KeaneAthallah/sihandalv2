<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Transaksi Penerimaan" :breadcrumbs="['Keuangan', 'Transaksi Penerimaan']">
            <x-slot name="actions">
                <a href="{{ route('transaksi-penerimaan.create') }}" class="btn-primary">
                    <x-heroicon-o-plus class="w-4 h-4"/>
                    Transaksi Baru
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    @if(session('success'))
        <x-alert type="success" :dismissible="true">{{ session('success') }}</x-alert>
    @endif
    @if($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-5">
        <x-stat-card title="Total Realisasi" value="Rp {{ number_format($totalRealisasi / 1000000000, 1, ',', '.') }} M" change="Dari seluruh transaksi" changeType="up" color="success">
            <x-slot name="icon">
                <x-heroicon-o-arrow-down-left class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Jumlah Transaksi" value="{{ $transaksis->total() }}" change="Transaksi Penerimaan aktif" changeType="up" color="info">
            <x-slot name="icon">
                <x-heroicon-o-document-text class="w-6 h-6"/>
            </x-slot>
        </x-stat-card>
    </div>

    <x-card :padding="false">
        <div class="flex items-center gap-3 flex-wrap px-5 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <label class="text-sm text-slate-500 font-medium">Sumber Dana</label>
                <select name="penerimaan_id" class="input" onchange="if(this.value) window.location.href = '{{ url('transaksi-penerimaan') }}?penerimaan_id=' + this.value; else window.location.href = '{{ url('transaksi-penerimaan') }}';">
                    <option value="">Semua</option>
                    @foreach($penerimaans as $p)
                        <option value="{{ $p->id }}" {{ ($filters['penerimaan_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->sumberDana?->nama_sumber_dana ?? $p->nama_sumber_dana }} ({{ $p->opd?->nama ?? 'Provinsi' }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[1000px]">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="text-left px-5 py-3 table-head w-[50px]">No</th>
                        <th class="text-left px-5 py-3 table-head w-[140px]">Nomor Registrasi</th>
                        <th class="text-left px-5 py-3 table-head w-[130px]">Tanggal</th>
                        <th class="text-left px-5 py-3 table-head w-[180px]">Sumber Dana</th>
                        <th class="text-left px-5 py-3 table-head">OPD</th>
                        <th class="text-right px-5 py-3 table-head w-[160px]">Realisasi</th>
                        <th class="text-center px-5 py-3 table-head w-[90px]">Jumlah BKU</th>
                        <th class="text-left px-5 py-3 table-head">Keterangan</th>
                        <th class="text-center px-5 py-3 table-head w-[160px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksis as $idx => $item)
                        <tr class="table-row">
                            <td class="px-5 py-3.5 text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 whitespace-nowrap">
                                    {{ $item->nomor_registrasi ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">{{ $item->tanggal?->format('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-primary/10 text-primary whitespace-nowrap">
                                    {{ $item->penerimaan?->sumberDana?->nama_sumber_dana ?? $item->penerimaan?->nama_sumber_dana ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 font-medium max-w-[220px] truncate">{{ $item->penerimaan?->opd?->nama ?? 'Provinsi' }}</td>
                            <td class="px-5 py-3.5 font-medium text-emerald-600 text-right whitespace-nowrap tabular-nums">
                                Rp {{ number_format($item->realisasi, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 rounded-lg text-xs font-semibold {{ $item->bkus->count() > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-400' }}">
                                    {{ $item->bkus->count() }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 max-w-[200px] truncate">{{ $item->keterangan ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" title="Lihat BKU"
                                        @click="$dispatch('open-detail-bku', {
                                            nomor_registrasi: @js($item->nomor_registrasi),
                                            tanggal: @js($item->tanggal?->format('d-m-Y')),
                                            keterangan: @js($item->keterangan),
                                            realisasi: @js((float) $item->realisasi),
                                            bkus: @js($item->bkus->map(fn ($b) => [
                                                'nomor_bku' => $b->nomor_bku,
                                                'tanggal_bku' => $b->tanggal_bku?->format('d-m-Y'),
                                                'nilai' => (float) $b->nilai,
                                                'rekening' => ($b->rekening?->kode ?? '').' '.($b->rekening?->nama ?? '-'),
                                            ])->values()->all()),
                                        }); $dispatch('open-modal', 'detail-bku')"
                                        class="icon-btn hover:text-blue-600 hover:bg-blue-50">
                                        <x-heroicon-o-eye class="w-4 h-4"/>
                                    </button>
                                    <a href="{{ route('transaksi-penerimaan.edit', $item) }}" title="Edit"
                                        class="icon-btn hover:text-amber-600 hover:bg-amber-50">
                                        <x-heroicon-o-pencil class="w-4 h-4"/>
                                    </a>
                                    <form method="POST" action="{{ route('transaksi-penerimaan.destroy', $item) }}"
                                        @submit.prevent="if(confirm('Yakin ingin menghapus transaksi ini?')) $el.submit()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus"
                                            class="icon-btn hover:text-red-600 hover:bg-red-50">
                                            <x-heroicon-o-trash class="w-4 h-4"/>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center">
                                <div class="inline-flex flex-col items-center">
                                    <div class="empty-icon">
                                        <x-heroicon-o-banknotes class="w-7 h-7"/>
                                    </div>
                                    <p class="empty-title">Belum ada transaksi penerimaan</p>
                                    <p class="empty-desc">Transaksi realisasi penerimaan akan tampil di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-slate-100 flex items-center justify-between">
            <p class="text-sm text-slate-500">Menampilkan <span class="font-medium text-slate-700">{{ $transaksis->total() }}</span> transaksi penerimaan</p>
            @if(method_exists($transaksis, 'links'))
                <div class="text-sm">
                    {{ $transaksis->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </x-card>

    {{-- Detail BKU Modal --}}
    <x-modal name="detail-bku" max-width="2xl">
        <div class="p-6" x-data="{
            t: {},
            formatRupiah(value) {
                const n = parseFloat(value) || 0;
                return 'Rp ' + n.toLocaleString('id-ID');
            },
        }" x-on:open-detail-bku.window="t = $event.detail">
            <div class="flex items-start justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Detail BKU</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="'Nomor Registrasi: ' + (t.nomor_registrasi || '-')"></p>
                </div>
                <button @click="$dispatch('close-modal', 'detail-bku')" class="icon-btn hover:bg-slate-100">
                    <x-heroicon-o-x-mark class="w-4 h-4"/>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-5">
                <div class="rounded-lg bg-slate-50 border border-slate-100 p-3">
                    <p class="text-xs text-slate-400 font-medium">Tanggal</p>
                    <p class="text-sm font-semibold text-slate-700 mt-0.5" x-text="t.tanggal || '-'"></p>
                </div>
                <div class="rounded-lg bg-slate-50 border border-slate-100 p-3">
                    <p class="text-xs text-slate-400 font-medium">Realisasi</p>
                    <p class="text-sm font-semibold text-emerald-600 mt-0.5" x-text="formatRupiah(t.realisasi)"></p>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-sm min-w-[560px]">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="text-left px-4 py-2.5 table-head w-10">No</th>
                            <th class="text-left px-4 py-2.5 table-head">Nomor BKU</th>
                            <th class="text-left px-4 py-2.5 table-head">Tanggal BKU</th>
                            <th class="text-right px-4 py-2.5 table-head">Nilai</th>
                            <th class="text-left px-4 py-2.5 table-head">Rekening</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(bku, i) in (t.bkus || [])" :key="i">
                            <tr>
                                <td class="px-4 py-2.5 text-slate-400 tabular-nums" x-text="i + 1"></td>
                                <td class="px-4 py-2.5 font-medium text-slate-700 whitespace-nowrap" x-text="bku.nomor_bku"></td>
                                <td class="px-4 py-2.5 text-slate-600 whitespace-nowrap" x-text="bku.tanggal_bku || '-'"></td>
                                <td class="px-4 py-2.5 text-right font-medium text-slate-700 whitespace-nowrap tabular-nums" x-text="formatRupiah(bku.nilai)"></td>
                                <td class="px-4 py-2.5 text-slate-500" x-text="bku.rekening || '-'"></td>
                            </tr>
                        </template>
                        <tr x-show="(t.bkus || []).length === 0">
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-400">Belum ada detail BKU.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </x-modal>
</x-app-layout>