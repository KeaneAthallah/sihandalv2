<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Pengeluaran" :breadcrumbs="['Keuangan', 'Pengeluaran', 'Edit']">
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
            <form action="{{ route('pengeluaran.update', $pengeluaran) }}" method="POST"
                  x-data="{
                      opdId: {{ json_encode((string) old('opd_id', $pengeluaran->opd_id)) }},
                      programId: {{ json_encode((string) old('program_id', '')) }},
                      kegiatanId: {{ json_encode((string) old('kegiatan_id', $pengeluaran->kegiatan_id)) }},
                      subKegiatanId: {{ json_encode((string) old('sub_kegiatan_id', $pengeluaran->sub_kegiatan_id)) }},
                      programsByOpd: {{ Js::from($programsByOpd) }},
                      kegiatansByProgram: {{ Js::from($kegiatansByProgram) }},
                      subKegiatansByKegiatan: {{ Js::from($subKegiatansByKegiatan) }},
                      belanjasBySubKegiatan: {{ Js::from($belanjasBySubKegiatan) }},
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
                @method('PUT')

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
                                    <option value="{{ $sd->id }}" {{ old('sumber_dana_id', $pengeluaran->sumber_dana_id) == $sd->id ? 'selected' : '' }}>{{ $sd->nama_sumber_dana }}</option>
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
                                <option value="{{ $rekening->id }}" {{ old('rekening_id', $pengeluaran->rekening_id) == $rekening->id ? 'selected' : '' }}>{{ $rekening->kode . ' - ' . $rekening->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rekening_id')" class="mt-1"/>
                        <p class="mt-1 text-xs text-slate-400">Rekening bertipe belanja.</p>
                    </div>

                    <div>
                        <x-input-label>Jumlah (Rp) <span class="text-red-500">*</span></x-input-label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                            <x-text-input type="number" name="jumlah" :value="old('jumlah', $pengeluaran->jumlah)" min="0" step="1" placeholder="0" class="pl-10" required/>
                        </div>
                        <x-input-error :messages="$errors->get('jumlah')" class="mt-1"/>
                    </div>

                    <div>
                        <x-input-label value="Keperluan" />
                        <x-text-input type="text" name="keperluan" :value="old('keperluan', $pengeluaran->keperluan)" placeholder="Contoh: Pengadaan perlengkapan kantor"/>
                        <x-input-error :messages="$errors->get('keperluan')" class="mt-1"/>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="No SP2D" />
                            <x-text-input type="text" name="no_sp2d" :value="old('no_sp2d', $pengeluaran->no_sp2d)" placeholder="Nomor SP2D"/>
                            <x-input-error :messages="$errors->get('no_sp2d')" class="mt-1"/>
                        </div>
                        <div>
                            <x-input-label value="Tanggal SP2D" />
                            <x-text-input type="date" name="tanggal_sp2d" :value="old('tanggal_sp2d', $pengeluaran->tanggal_sp2d?->format('Y-m-d'))"/>
                            <x-input-error :messages="$errors->get('tanggal_sp2d')" class="mt-1"/>
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Tanggal"/>
                        <x-text-input type="date" name="tanggal" :value="old('tanggal', $pengeluaran->tanggal?->format('Y-m-d'))"/>
                        <x-input-error :messages="$errors->get('tanggal')" class="mt-1"/>
                    </div>
                </div>

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
