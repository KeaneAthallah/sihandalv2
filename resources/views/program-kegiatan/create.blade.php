<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Program & Kegiatan" :breadcrumbs="['Program & Kegiatan', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <form action="{{ route('program-kegiatan.store') }}" method="POST">
                @csrf
                <div class="space-y-6">
                    {{-- Section: OPD --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                <x-heroicon-o-building-office-2 class="w-4 h-4 text-primary"/>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-700">Informasi OPD</h3>
                        </div>
                        <div>
                            <x-input-label for="opd_id" value="OPD" />
                            <select name="opd_id" id="opd_id" required class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                                <option value="">Pilih OPD</option>
                                @foreach($opds as $opd)
                                    <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('opd_id')" class="mt-1" />
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    {{-- Section: Kegiatan --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                                <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-purple-600"/>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-700">Detail Kegiatan</h3>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="kode_kegiatan" value="Kode Kegiatan" />
                                    <x-text-input type="text" name="kode_kegiatan" id="kode_kegiatan" value="{{ old('kode_kegiatan') }}" placeholder="Masukkan kode kegiatan" required class="mt-1.5" />
                                    <x-input-error :messages="$errors->get('kode_kegiatan')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="kode_sub_kegiatan" value="Kode Sub Kegiatan" />
                                    <x-text-input type="text" name="kode_sub_kegiatan" id="kode_sub_kegiatan" value="{{ old('kode_sub_kegiatan') }}" placeholder="Opsional" class="mt-1.5" />
                                    <x-input-error :messages="$errors->get('kode_sub_kegiatan')" class="mt-1" />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="nama_kegiatan" value="Nama Kegiatan" />
                                <x-text-input type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan') }}" placeholder="Masukkan nama kegiatan" required class="mt-1.5" />
                                <x-input-error :messages="$errors->get('nama_kegiatan')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="nama_sub_kegiatan" value="Nama Sub Kegiatan" />
                                <x-text-input type="text" name="nama_sub_kegiatan" id="nama_sub_kegiatan" value="{{ old('nama_sub_kegiatan') }}" placeholder="Opsional" class="mt-1.5" />
                                <x-input-error :messages="$errors->get('nama_sub_kegiatan')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    {{-- Section: Rekening --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                                <x-heroicon-o-wallet class="w-4 h-4 text-emerald-600"/>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-700">Detail Rekening</h3>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="kode_rekening" value="Kode Rekening" />
                                <x-text-input type="text" name="kode_rekening" id="kode_rekening" value="{{ old('kode_rekening') }}" placeholder="Opsional" class="mt-1.5" />
                                <x-input-error :messages="$errors->get('kode_rekening')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="nama_rekening" value="Nama Rekening" />
                                <x-text-input type="text" name="nama_rekening" id="nama_rekening" value="{{ old('nama_rekening') }}" placeholder="Opsional" class="mt-1.5" />
                                <x-input-error :messages="$errors->get('nama_rekening')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    {{-- Section: Anggaran --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                                <x-heroicon-o-banknotes class="w-4 h-4 text-amber-600"/>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-700">Anggaran</h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <x-input-label for="sumber_dana" value="Sumber Dana" />
                                <x-text-input type="text" name="sumber_dana" id="sumber_dana" value="{{ old('sumber_dana') }}" placeholder="Masukkan sumber dana" required class="mt-1.5" />
                                <x-input-error :messages="$errors->get('sumber_dana')" class="mt-1" />
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="pagu" value="Pagu Anggaran" />
                                    <x-text-input type="number" name="pagu" id="pagu" value="{{ old('pagu', '0') }}" placeholder="0" step="0.01" min="0" required class="mt-1.5" />
                                    <x-input-error :messages="$errors->get('pagu')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="realisasi" value="Realisasi" />
                                    <x-text-input type="number" name="realisasi" id="realisasi" value="{{ old('realisasi', '0') }}" placeholder="0" step="0.01" min="0" class="mt-1.5" />
                                    <p class="text-xs text-slate-400 mt-1.5">Dapat dikosongkan atau diisi nanti.</p>
                                    <x-input-error :messages="$errors->get('realisasi')" class="mt-1" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-secondary-button type="button" onclick="window.location='{{ route('program-kegiatan.index') }}'">
                        Batal
                    </x-secondary-button>
                    <x-primary-button>
                        Simpan Program
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
