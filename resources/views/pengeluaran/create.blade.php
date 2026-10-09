<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Pengeluaran" :breadcrumbs="['Keuangan', 'Pengeluaran', 'Tambah Baru']">
            <x-slot name="actions">
                <a href="{{ route('pengeluaran.index') }}"
                    class="btn-secondary">
                    Kembali
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        <x-card title="Formulir Pengeluaran">
            <form action="{{ route('pengeluaran.store') }}" method="POST"
                  x-data="{
                      opdId: {{ json_encode((string) old('opd_id', '')) }},
                      programId: {{ json_encode((string) old('program_id', '')) }},
                      kegiatanId: {{ json_encode((string) old('kegiatan_id', '')) }},
                      subKegiatanId: {{ json_encode((string) old('sub_kegiatan_id', '')) }},
                      pdId: {{ json_encode((string) old('permintaan_dana_id', '')) }},
                      programsByOpd: {{ Js::from($programsByOpd) }},
                      kegiatansByProgram: {{ Js::from($kegiatansByProgram) }},
                      subKegiatansByKegiatan: {{ Js::from($subKegiatansByKegiatan) }},
                      belanjasBySubKegiatan: {{ Js::from($belanjasBySubKegiatan) }},
                      permintaanDanas: {{ Js::from($permintaanDanas) }},
                      get programOptions() {
                          return this.programsByOpd[this.opdId] || [];
                      },
                      get kegiatanOptions() {
                          return this.kegiatansByProgram[this.programId] || [];
                      },
                      get subKegiatanOptions() {
                          return this.subKegiatansByKegiatan[this.kegiatanId] || [];
                      },
                      get belanjaOptions() {
                          return this.belanjasBySubKegiatan[this.subKegiatanId] || [];
                      },
                      get selectedPd() {
                          return this.permintaanDanas.find(function (p) { return String(p.id) === String(this.pdId); }, this) || null;
                      },
                      formatRupiah(value) {
                          var n = parseFloat(value) || 0;
                          return 'Rp ' + n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                      },
                      onOpdChange() {
                          this.programId = '';
                          this.kegiatanId = '';
                          this.subKegiatanId = '';
                      },
                      onProgramChange() {
                          this.kegiatanId = '';
                          this.subKegiatanId = '';
                      },
                      onKegiatanChange() {
                          this.subKegiatanId = '';
                      }
                  }">
                @csrf

                @if(auth()->user()->isAdmin() && $permintaanDanas->isNotEmpty())
                <div class="rounded-xl border border-primary/30 bg-primary/5 p-4 mb-5">
                    <x-input-label value="Ambil dari Permintaan Dana" />
                    <select name="permintaan_dana_id" x-model="pdId"
                        class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        <option value="">— Buat manual —</option>
                        <template x-for="p in permintaanDanas" :key="p.id">
                            <option :value="p.id" x-text="p.label"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Bila dipilih, seluruh field keuangan diambil otomatis dari permintaan dana — Anda hanya mengisi No SP2D dan Tanggal SP2D.</p>
                    <x-input-error :messages="$errors->get('permintaan_dana_id')" class="mt-1"/>
                </div>
                @endif

                {{-- Hasil mirror dari permintaan dana --}}
                <template x-if="selectedPd">
                    <div class="space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Detail Permintaan Dana</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-xs text-slate-400">Nomor Permintaan Dana</p>
                                    <p class="font-medium text-slate-700" x-text="selectedPd.nomor"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">OPD</p>
                                    <p class="font-medium text-slate-700" x-text="selectedPd.opd"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Sumber Dana</p>
                                    <p class="font-medium text-slate-700" x-text="selectedPd.sumberDana"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Kegiatan</p>
                                    <p class="font-medium text-slate-700" x-text="selectedPd.kegiatan"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Sub Kegiatan</p>
                                    <p class="font-medium text-slate-700" x-text="selectedPd.subKegiatan"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Belanja</p>
                                    <p class="font-medium text-slate-700" x-text="selectedPd.belanja"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Rekening</p>
                                    <p class="font-medium text-slate-700" x-text="selectedPd.rekening"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Jumlah</p>
                                    <p class="font-semibold text-slate-800" x-text="formatRupiah(selectedPd.jumlah)"></p>
                                </div>
                            </div>

                            <div class="mt-3">
                                <p class="text-xs text-slate-400">Keperluan</p>
                                <p class="text-sm text-slate-700" x-text="selectedPd.keperluan"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="No SP2D" />
                                <x-text-input type="text" name="no_sp2d" :value="old('no_sp2d')" placeholder="Nomor SP2D" required/>
                                <x-input-error :messages="$errors->get('no_sp2d')" class="mt-1"/>
                            </div>
                            <div>
                                <x-input-label value="Tanggal SP2D" />
                                <x-text-input type="text" name="tanggal_sp2d" :value="old('tanggal_sp2d')" required class="datepicker"/>
                                <x-input-error :messages="$errors->get('tanggal_sp2d')" class="mt-1"/>
                            </div>
                        </div>

                        <div>
                            <x-input-label value="Tanggal"/>
                            <x-text-input type="text" name="tanggal" :value="old('tanggal', date('Y-m-d'))" class="datepicker"/>
                            <x-input-error :messages="$errors->get('tanggal')" class="mt-1"/>
                        </div>
                    </div>
                </template>

                {{-- Form manual --}}
                <template x-if="!selectedPd">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label>OPD <span class="text-red-500">*</span></x-input-label>
                                <select name="opd_id" x-model="opdId" @change="onOpdChange()"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                    <option value="">Pilih OPD</option>
                                    @foreach($opds as $opd)
                                        <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('opd_id')" class="mt-1"/>
                            </div>
                            <div>
                                <x-input-label>Sumber Dana <span class="text-red-500">*</span></x-input-label>
                                <select name="sumber_dana_id"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                    <option value="">Pilih Sumber Dana</option>
                                    @foreach($sumberDanas as $sd)
                                        <option value="{{ $sd->id }}" {{ old('sumber_dana_id') == $sd->id ? 'selected' : '' }}>{{ $sd->nama_sumber_dana }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('sumber_dana_id')" class="mt-1"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Program" />
                                <select name="program_id" x-model="programId" @change="onProgramChange()"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                    <option value="">Pilih Program</option>
                                    <template x-for="p in programOptions" :key="p.id">
                                        <option :value="p.id" x-text="p.label"></option>
                                    </template>
                                </select>
                                <x-input-error :messages="$errors->get('program_id')" class="mt-1"/>
                            </div>
                            <div>
                                <x-input-label value="Kegiatan" />
                                <select name="kegiatan_id" x-model="kegiatanId" @change="onKegiatanChange()"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                    <option value="">Pilih Kegiatan</option>
                                    <template x-for="k in kegiatanOptions" :key="k.id">
                                        <option :value="k.id" x-text="k.label"></option>
                                    </template>
                                </select>
                                <x-input-error :messages="$errors->get('kegiatan_id')" class="mt-1"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="Sub Kegiatan" />
                                <select name="sub_kegiatan_id" x-model="subKegiatanId"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                    <option value="">Pilih Sub Kegiatan</option>
                                    <template x-for="s in subKegiatanOptions" :key="s.id">
                                        <option :value="s.id" x-text="s.label"></option>
                                    </template>
                                </select>
                                <x-input-error :messages="$errors->get('sub_kegiatan_id')" class="mt-1"/>
                            </div>
                            <div>
                                <x-input-label value="Belanja" />
                                <select name="belanja_id"
                                    class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                    <option value="">Pilih Belanja (Opsional)</option>
                                    <template x-for="b in belanjaOptions" :key="b.id">
                                        <option :value="b.id" x-text="b.label"></option>
                                    </template>
                                </select>
                                <x-input-error :messages="$errors->get('belanja_id')" class="mt-1"/>
                            </div>
                        </div>

                        <div>
                            <x-input-label value="Rekening" />
                            <select name="rekening_id"
                                class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih Rekening (Opsional)</option>
                                @foreach($rekenings as $rekening)
                                    <option value="{{ $rekening->id }}" {{ old('rekening_id') == $rekening->id ? 'selected' : '' }}>{{ $rekening->kode . ' - ' . $rekening->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('rekening_id')" class="mt-1"/>
                            <p class="mt-1 text-xs text-slate-400">Rekening bertipe belanja.</p>
                        </div>

                        <div>
                            <x-input-label>Jumlah (Rp) <span class="text-red-500">*</span></x-input-label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                                <x-text-input type="number" name="jumlah" :value="old('jumlah')" min="0" step="1" placeholder="0" class="pl-10" required/>
                            </div>
                            <x-input-error :messages="$errors->get('jumlah')" class="mt-1"/>
                        </div>

                        <div>
                            <x-input-label value="Keperluan" />
                            <x-text-input type="text" name="keperluan" :value="old('keperluan')" placeholder="Contoh: Pengadaan perlengkapan kantor"/>
                            <x-input-error :messages="$errors->get('keperluan')" class="mt-1"/>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label value="No SP2D" />
                                <x-text-input type="text" name="no_sp2d" :value="old('no_sp2d')" placeholder="Nomor SP2D"/>
                                <x-input-error :messages="$errors->get('no_sp2d')" class="mt-1"/>
                            </div>
                            <div>
                                <x-input-label value="Tanggal SP2D" />
                                <x-text-input type="text" name="tanggal_sp2d" :value="old('tanggal_sp2d')" class="datepicker"/>
                                <x-input-error :messages="$errors->get('tanggal_sp2d')" class="mt-1"/>
                            </div>
                        </div>

                        <div>
                            <x-input-label value="Tanggal"/>
                            <x-text-input type="text" name="tanggal" :value="old('tanggal', date('Y-m-d'))" class="datepicker"/>
                            <x-input-error :messages="$errors->get('tanggal')" class="mt-1"/>
                        </div>
                    </div>
                </template>

                <div class="mt-5 flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('pengeluaran.index') }}"
                       class="btn-secondary">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
