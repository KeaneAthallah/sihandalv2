<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Program & Kegiatan" :breadcrumbs="['Program & Kegiatan', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Tambah Program">
            <form action="{{ route('program-kegiatan.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label for="opd_id" value="OPD" />
                        <select name="opd_id" id="opd_id" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')" class="mt-1" />
                    </div>

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

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('program-kegiatan.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                        Simpan
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
