<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Buat Permintaan Dana" :breadcrumbs="['Transaksi', 'Permintaan Dana', 'Buat Baru']">
            <x-slot name="actions">
                <a href="{{ route('permintaan-dana.index') }}"
                    class="btn-secondary">
                    Kembali
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        <x-card title="Formulir Permintaan Dana">
            <form action="{{ route('permintaan-dana.store') }}" method="POST"
                  x-data="{
                      opdId: {{ json_encode((string) old('opd_id', '')) }},
                      programId: {{ json_encode((string) old('program_id', '')) }},
                      kegiatanId: {{ json_encode((string) old('kegiatan_id', '')) }},
                      subKegiatanId: {{ json_encode((string) old('sub_kegiatan_id', '')) }},
                      belanjaId: {{ json_encode((string) old('belanja_id', '')) }},
                      sumberDanaId: {{ json_encode((string) old('sumber_dana_id', '')) }},
                      jumlahInput: {{ json_encode((int) old('jumlah', 0)) }},
                      programsByOpd: {{ Js::from($programsByOpd) }},
                      kegiatansByProgram: {{ Js::from($kegiatansByProgram) }},
                      subKegiatansByKegiatan: {{ Js::from($subKegiatansByKegiatan) }},
                      belanjasBySubKegiatan: {{ Js::from($belanjasBySubKegiatan) }},
                      sumberDanaList: {{ Js::from($sumberDanas->map(fn ($sd) => ['id' => (string) $sd->id, 'label' => $sd->nama_sumber_dana])->values()) }},
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
                      get sumberDanaOptions() {
                          // Hanya sumber dana yang dimiliki belanja
                          // pada sub kegiatan yang dipilih.
                          var ids = {};
                          this.belanjaOptions.forEach(function (b) {
                              ids[b.sumber_dana_id] = true;
                          });
                          return this.sumberDanaList.filter(function (s) {
                              return ids[s.id] === true;
                          });
                      },
                      get selectedBelanja() {
                          return this.belanjaOptions.find(function (b) { return b.id === this.belanjaId; }, this) || null;
                      },
                      get jumlahTersedia() {
                          if (!this.selectedBelanja) {
                              return 0;
                          }
                          return Math.min(this.selectedBelanja.pagu_tersisa, this.selectedBelanja.kas_tersedia);
                      },
                      formatRupiah(value) {
                          var n = parseFloat(value) || 0;
                          return 'Rp ' + n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                      },
                      onOpdChange() {
                          this.programId = '';
                          this.kegiatanId = '';
                          this.subKegiatanId = '';
                          this.belanjaId = '';
                          this.sumberDanaId = '';
                      },
                      onProgramChange() {
                          this.kegiatanId = '';
                          this.subKegiatanId = '';
                          this.belanjaId = '';
                          this.sumberDanaId = '';
                      },
                      onKegiatanChange() {
                          this.subKegiatanId = '';
                          this.belanjaId = '';
                          this.sumberDanaId = '';
                      },
                      onSubKegiatanChange() {
                          this.belanjaId = '';
                          this.sumberDanaId = '';
                      },
                      onBelanjaChange(belanjaId) {
                          // Sumber dana mengikuti belanja — satu-satunya
                          // pilihan yang valid untuk belanja ini.
                          var b = this.belanjaOptions.find(function (o) { return o.id === belanjaId; });
                          this.sumberDanaId = b ? b.sumber_dana_id : '';
                      }
                  }">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label>OPD <span class="text-red-500">*</span></x-input-label>
                            <select name="opd_id" x-model="opdId" @change="onOpdChange()"
                                class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                <option value="">Pilih OPD</option>
                                @foreach($opds as $opd)
                                    <option value="{{ $opd->id }}">{{ $opd->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('opd_id')" class="mt-1"/>
                        </div>
                        <div>
                            <x-input-label>Sumber Dana <span class="text-red-500">*</span></x-input-label>
                            <select name="sumber_dana_id" x-model="sumberDanaId" :disabled="!belanjaId" required
                                class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition disabled:bg-surface-alt disabled:text-content-muted disabled:cursor-not-allowed">
                                <option value="">Pilih Sumber Dana</option>
                                <template x-for="sd in sumberDanaOptions" :key="sd.id">
                                    <option :value="sd.id" x-text="sd.label"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('sumber_dana_id')" class="mt-1"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Program" />
                            <select name="program_id" x-model="programId" @change="onProgramChange()"
                                class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
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
                                class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
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
                            <select name="sub_kegiatan_id" x-model="subKegiatanId" @change="onSubKegiatanChange()"
                                class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih Sub Kegiatan</option>
                                <template x-for="s in subKegiatanOptions" :key="s.id">
                                    <option :value="s.id" x-text="s.label"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('sub_kegiatan_id')" class="mt-1"/>
                        </div>
                        <div>
                            <x-input-label value="Belanja" />
                            <select name="belanja_id" x-model="belanjaId" @change="onBelanjaChange($event.target.value)" required
                                class="w-full px-3 py-2 bg-card border border-border-strong rounded-lg text-sm text-content-secondary focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih Belanja</option>
                                <template x-for="b in belanjaOptions" :key="b.id">
                                    <option :value="b.id" x-text="b.label"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('belanja_id')" class="mt-1"/>
                            <div x-show="selectedBelanja" class="mt-2 rounded-lg border border-border bg-surface p-3 text-xs space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-content-muted">Pagu</span>
                                    <span class="font-semibold text-content-secondary" x-text="formatRupiah(selectedBelanja ? selectedBelanja.pagu : 0)"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-content-muted">Penerimaan</span>
                                    <span class="font-semibold text-content-secondary" x-text="formatRupiah(selectedBelanja ? selectedBelanja.penerimaan : 0)"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-content-muted">Dana commit</span>
                                    <span class="font-semibold text-content-secondary" x-text="formatRupiah(selectedBelanja ? selectedBelanja.dana_di_commit : 0)"></span>
                                </div>
                                <div class="flex items-center justify-between border-t border-border pt-1">
                                    <span class="text-content-muted">Pagu tersisa</span>
                                    <span class="font-semibold text-content-secondary" x-text="formatRupiah(selectedBelanja ? selectedBelanja.pagu_tersisa : 0)"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-content-muted">Kas tersedia</span>
                                    <span class="font-semibold text-content-secondary" x-text="formatRupiah(selectedBelanja ? selectedBelanja.kas_tersedia : 0)"></span>
                                </div>
                                <p class="text-content-muted">Yang dapat dipakai adalah nilai terkecil dari pagu tersisa dan kas tersedia.</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <x-input-label>Jumlah (Rp) <span class="text-red-500">*</span></x-input-label>
                        <input type="range" min="0" :max="jumlahTersedia > 0 ? jumlahTersedia : 1" step="1000"
                            x-model.number="jumlahInput" :disabled="!sumberDanaId"
                            class="w-full accent-primary disabled:opacity-50 disabled:cursor-not-allowed">
                        <x-text-input type="number" name="jumlah" x-model.number="jumlahInput" min="1" step="1" placeholder="0" required
                            class="mt-2"/>
                        <p class="mt-1 text-xs text-content-muted">
                            Maksimal yang dapat dipakai:
                            <span class="font-semibold text-content-secondary" x-text="formatRupiah(jumlahTersedia)"></span>
                            — nilai terkecil dari pagu tersisa dan kas tersedia.
                        </p>
                        <x-input-error :messages="$errors->get('jumlah')" class="mt-1"/>
                    </div>

                    <div>
                        <x-input-label>Keperluan <span class="text-red-500">*</span></x-input-label>
                        <x-text-input type="text" name="keperluan" :value="old('keperluan')" placeholder="Contoh: Pengadaan perlengkapan kantor" required/>
                        <x-input-error :messages="$errors->get('keperluan')" class="mt-1"/>
                    </div>

                    <div>
                        <x-input-label value="Tanggal"/>
                        <x-text-input type="text" name="tanggal" :value="old('tanggal', date('Y-m-d'))" class="datepicker"/>
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-1"/>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-border-light">
                        <a href="{{ route('permintaan-dana.index') }}"
                           class="btn-secondary">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-primary text-white text-sm font-semibold rounded-lg hover:bg-primary-dark transition">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
