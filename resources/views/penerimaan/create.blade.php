<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Master Data Penerimaan" :breadcrumbs="['Keuangan', 'Master Data Penerimaan', 'Tambah Baru']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Master Penerimaan Baru">
            <form action="{{ route('master-data.penerimaan.store') }}" method="POST"
                  x-data="{
                      rekeningId: {{ json_encode((string) old('rekening_id', '')) }},
                      subRekeningId: {{ json_encode((string) old('sub_rekening_id', '')) }},
                      subRekeningsByParent: {{ Js::from($subRekeningsByParent) }},
                      get subOptions() {
                          return this.subRekeningsByParent[this.rekeningId] || [];
                      },
                      onRekeningChange() {
                          this.subRekeningId = '';
                      }
                  }">
                @csrf

                <div class="space-y-4">
                    <div>
                        <x-input-label value="OPD" />
                        <select name="opd_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')" />
                    </div>

                    <div>
                        <x-input-label value="Rekening Utama" />
                        <select name="rekening_id" x-model="rekeningId" @change="onRekeningChange()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Pilih Rekening Utama (Opsional)</option>
                            @foreach($rekenings as $rekening)
                                <option value="{{ $rekening->id }}">{{ $rekening->kode }} - {{ $rekening->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rekening_id')" />
                    </div>

                    <div x-show="subOptions().length > 0" x-cloak>
                        <x-input-label value="Sub Rekening (Opsional)" />
                        <select name="sub_rekening_id" x-model="subRekeningId" class="mt-1 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Pilih Sub Rekening</option>
                            <template x-for="r in subOptions" :key="r.id">
                                <option :value="r.id" x-text="r.label"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('sub_rekening_id')" />
                        <p class="mt-1 text-xs text-slate-400">Detail rekening dari rekening utama yang dipilih.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Tahun Anggaran" />
                            <select name="tahun_anggaran_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih Tahun Anggaran</option>
                                @foreach($tahunAnggarans as $ta)
                                    <option value="{{ $ta->id }}" {{ old('tahun_anggaran_id') == $ta->id ? 'selected' : '' }}>{{ $ta->tahun }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tahun_anggaran_id')" />
                        </div>
                        <div>
                            <x-input-label value="Target" />
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                <x-text-input name="target" type="number" :value="old('target', '0')" step="0.01" min="0" placeholder="0" class="pl-10" required />
                            </div>
                            <x-input-error :messages="$errors->get('target')" />
                            <p class="mt-1 text-xs text-slate-400">Realisasi dicatat melalui Transaksi Penerimaan secara terpisah.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('master-data.penerimaan.index') }}" class="btn-secondary">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
