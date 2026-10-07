<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Transaksi Penerimaan" :breadcrumbs="['Keuangan', 'Transaksi Penerimaan', 'Tambah Baru']" />
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-card title="Form Transaksi Penerimaan Baru">
            <form action="{{ route('transaksi-penerimaan.store') }}" method="POST">
                @csrf

                <div
                    x-data="{
                        realisasi: {{ json_encode((string) old('realisasi', '')) }},
                        bkus: {{ json_encode(old('bkus', [])) }},
                        penerimaanId: {{ json_encode((string) old('penerimaan_id', '')) }},
                        opds: {{ Js::from($opds->map(fn ($o) => ['id' => (string) $o->id, 'label' => $o->nama])->values()) }},
                        opdByPenerimaan: {{ Js::from($penerimaans->mapWithKeys(fn ($p) => [(string) $p->id => (string) ($p->opd_id ?? '')])->all()) }},
                        rekenings: {{ Js::from($rekenings->map(fn ($r) => ['id' => (string) $r->id, 'label' => $r->kode.' - '.$r->nama])->values()) }},
                        subRekeningsByParent: {{ Js::from($subRekeningsByParent) }},
                        bankOptions: {{ Js::from($rekeningBanks->map(fn ($b) => ['id' => (string) $b->id, 'label' => $b->label])->values()) }},
                        showBankModal: false,
                        savingBank: false,
                        bankForm: { bank_name: '', account_number: '', account_name: '' },
                        bankErrors: {},
                        async saveBank() {
                            this.savingBank = true;
                            this.bankErrors = {};
                            try {
                                const res = await fetch('{{ route('rekening-bank.store') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify(this.bankForm)
                                });
                                const data = await res.json();
                                if (!res.ok) {
                                    this.bankErrors = data.errors || {};
                                    if (!Object.keys(this.bankErrors).length) {
                                        this.bankErrors = { bank_name: [data.message || 'Gagal menyimpan rekening bank.'] };
                                    }
                                    return;
                                }
                                this.bankOptions.push({ id: String(data.id), label: data.label });
                                if (this.bkus.length > 0) {
                                    this.bkus[this.bkus.length - 1].rekening_bank_id = String(data.id);
                                }
                                this.bankForm = { bank_name: '', account_number: '', account_name: '' };
                                this.showBankModal = false;
                            } catch (e) {
                                this.bankErrors = { bank_name: ['Gagal menyimpan rekening bank.'] };
                            } finally {
                                this.savingBank = false;
                            }
                        },
                        getSubOptions(bku) {
                            return this.subRekeningsByParent[bku.rekening_id] || [];
                        },
                        get totalBku() {
                            return this.bkus.reduce((sum, b) => sum + (parseFloat(b.nilai) || 0), 0);
                        },
                        get isMatch() {
                            if (this.bkus.length === 0) {
                                return true;
                            }
                            return Math.abs(this.totalBku - (parseFloat(this.realisasi) || 0)) < 0.0001;
                        },
                        formatRupiah(value) {
                            const n = parseFloat(value) || 0;
                            return 'Rp ' + n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                        },
                        addBku() {
                            this.bkus.push({
                                id: null, opd_id: this.opdByPenerimaan[this.penerimaanId] || '',
                                nomor_bku: '', tanggal_bku: '', nilai: '',
                                rekening_id: '', sub_rekening_id: '', rekening_bank_id: ''
                            });
                        },
                        removeBku(index) {
                            this.bkus.splice(index, 1);
                        },
                    }"
                    class="space-y-8"
                >
                    {{-- Informasi Transaksi --}}
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-800 border-b border-slate-100 pb-2">Informasi Transaksi</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Nomor Registrasi" />
                                <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500">
                                    Digenerate otomatis oleh sistem (format REG-XXXXX/tahun)
                                </div>
                            </div>

                            <div>
                                <x-input-label value="Tanggal" />
                                <x-text-input name="tanggal" type="text" :value="old('tanggal', now()->format('Y-m-d'))" required class="datepicker" />
                                <x-input-error :messages="$errors->get('tanggal')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label value="Penerimaan" />
                            <select name="penerimaan_id" x-model="penerimaanId" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                <option value="">Pilih Penerimaan</option>
                                @foreach($penerimaans as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->nama_penerimaan ?? '-' }} - {{ $p->opd?->nama ?? 'Provinsi' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('penerimaan_id')" />
                        </div>

                        <div>
                            <x-input-label value="Sumber Dana" />
                            <select name="sumber_dana_id"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                <option value="">Pilih Sumber Dana</option>
                                @foreach($sumberDanas as $sd)
                                    <option value="{{ $sd->id }}">{{ $sd->nama_sumber_dana }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('sumber_dana_id')" class="mt-1"/>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Realisasi / Nilai Transaksi" />
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                    <x-text-input name="realisasi" type="number" x-model="realisasi" step="0.01" min="0" placeholder="0" class="pl-10" required />
                                </div>
                                <x-input-error :messages="$errors->get('realisasi')" />
                            </div>

                            <div>
                                <x-input-label value="Keterangan" />
                                <x-text-input name="keterangan" type="text" :value="old('keterangan')" placeholder="Tambahkan keterangan..." />
                                <x-input-error :messages="$errors->get('keterangan')" />
                            </div>
                        </div>
                    </div>

                    {{-- Detail BKU --}}
                    <div>
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-4">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-800">Detail BKU</h3>
                                <p class="mt-0.5 text-xs text-slate-400">Setiap baris BKU mencatat OPD, rekening utama, sub rekening dan rekening banknya sendiri.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="showBankModal = true; bankErrors = {};" class="btn-secondary !py-1.5 !px-3 text-xs">
                                    <x-heroicon-o-plus class="w-3.5 h-3.5 inline mr-1" />
                                    Tambah Bank
                                </button>
                                <button type="button" @click="addBku()" class="btn-secondary !py-1.5 !px-3 text-xs">
                                    <x-heroicon-o-plus class="w-3.5 h-3.5 inline mr-1" />
                                    Tambah BKU
                                </button>
                            </div>
                        </div>

                        <x-input-error :messages="$errors->get('bkus')" class="mb-3" />

                        <div class="space-y-4">
                            {{-- Empty state --}}
                            <div x-show="bkus.length === 0" class="rounded-xl border border-dashed border-slate-300 bg-slate-50/50 p-6 text-center">
                                <p class="text-sm text-slate-500">Belum ada data BKU.</p>
                            </div>

                            <template x-for="(bku, index) in bkus" :key="index">
                                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide" x-text="'BKU #' + (index + 1)"></p>
                                        <button type="button" @click="removeBku(index)"
                                            class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg px-2 py-1 transition">
                                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <x-input-label value="Nomor BKU" class="text-xs" />
                                            <input type="text" :name="'bkus[' + index + '][nomor_bku]'" x-model="bku.nomor_bku"
                                                class="input" placeholder="Contoh: BKU-001" required />
                                        </div>
                                        <div>
                                            <x-input-label value="Tanggal BKU" class="text-xs" />
                                            <input type="text" :name="'bkus[' + index + '][tanggal_bku]'" x-model="bku.tanggal_bku"
                                                class="input datepicker" required />
                                        </div>
                                        <div>
                                            <x-input-label value="Nilai" class="text-xs" />
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-medium">Rp</span>
                                                <input type="number" :name="'bkus[' + index + '][nilai]'" x-model="bku.nilai"
                                                    class="input pl-9" step="0.01" min="0" placeholder="0" required />
                                            </div>
                                        </div>
                                        <div>
                                            <x-input-label value="OPD" class="text-xs" />
                                            <select :name="'bkus[' + index + '][opd_id]'" x-model="bku.opd_id"
                                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                                <option value="">Pilih OPD</option>
                                                <template x-for="o in opds" :key="o.id">
                                                    <option :value="o.id" x-text="o.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <x-input-label value="Rekening Utama" class="text-xs" />
                                            <select :name="'bkus[' + index + '][rekening_id]'" x-model="bku.rekening_id"
                                                @change="bku.sub_rekening_id = ''"
                                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                                <option value="">Pilih Rekening Utama (Opsional)</option>
                                                <template x-for="r in rekenings" :key="r.id">
                                                    <option :value="r.id" x-text="r.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <x-input-label value="Sub Rekening" class="text-xs" />
                                            <select :name="'bkus[' + index + '][sub_rekening_id]'" x-model="bku.sub_rekening_id"
                                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                                <option value="">Pilih Sub Rekening (Opsional)</option>
                                                <template x-for="r in getSubOptions(bku)" :key="r.id">
                                                    <option :value="r.id" x-text="r.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <x-input-label value="Rekening Bank" class="text-xs" />
                                            <select :name="'bkus[' + index + '][rekening_bank_id]'" x-model="bku.rekening_bank_id"
                                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                                <option value="">Pilih Rekening Bank (Opsional)</option>
                                                <template x-for="b in bankOptions" :key="b.id">
                                                    <option :value="b.id" x-text="b.label"></option>
                                                </template>
                                            </select>
                                            <p class="mt-1 text-xs text-slate-400">Rekening bank fisik tempat BKU ini masuk. Belum ada datanya? Gunakan tombol <span class="font-medium">Tambah Bank</span> di atas.</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Rekap Total --}}
                        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Total Nilai BKU</p>
                                    <p class="font-semibold text-slate-800 mt-0.5" x-text="formatRupiah(totalBku)"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Nilai Transaksi</p>
                                    <p class="font-semibold text-slate-800 mt-0.5" x-text="formatRupiah(realisasi)"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 font-medium">Status</p>
                                    <p class="mt-0.5 font-semibold"
                                        x-text="bkus.length === 0 ? 'Belum ada BKU' : (isMatch ? '✓ Sesuai' : '✕ Tidak sesuai')"
                                        :class="bkus.length === 0 ? 'text-slate-500' : (isMatch ? 'text-emerald-600' : 'text-red-500')"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                @include('transaksi-penerimaan._bank-modal')
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('transaksi-penerimaan.index') }}" class="btn-secondary">
                        Batal
                    </a>
                    <button type="submit" :disabled="!isMatch" class="btn-primary">Simpan</button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
