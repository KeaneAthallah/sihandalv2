<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Program & Kegiatan" :breadcrumbs="['Program & Kegiatan', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Form Edit Program & Kegiatan</h2>
            </div>
            <form action="{{ route('program-kegiatan.update', $program) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">OPD <span class="text-red-500">*</span></label>
                        <select name="opd_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id', $program->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        @error('opd_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="kode_kegiatan" value="{{ old('kode_kegiatan', $program->kode_kegiatan) }}" placeholder="Masukkan kode kegiatan" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                        @error('kode_kegiatan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Kegiatan <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $program->nama_kegiatan) }}" placeholder="Masukkan nama kegiatan" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                        @error('nama_kegiatan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Sub Kegiatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="kode_sub_kegiatan" value="{{ old('kode_sub_kegiatan', $program->kode_sub_kegiatan) }}" placeholder="Masukkan kode sub kegiatan" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('kode_sub_kegiatan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Sub Kegiatan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="nama_sub_kegiatan" value="{{ old('nama_sub_kegiatan', $program->nama_sub_kegiatan) }}" placeholder="Masukkan nama sub kegiatan" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('nama_sub_kegiatan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Rekening <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="kode_rekening" value="{{ old('kode_rekening', $program->kode_rekening) }}" placeholder="Masukkan kode rekening" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('kode_rekening')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Rekening <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="nama_rekening" value="{{ old('nama_rekening', $program->nama_rekening) }}" placeholder="Masukkan nama rekening" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('nama_rekening')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Sumber Dana <span class="text-red-500">*</span></label>
                        <input type="text" name="sumber_dana" value="{{ old('sumber_dana', $program->sumber_dana) }}" placeholder="Masukkan sumber dana" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                        @error('sumber_dana')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Pagu <span class="text-red-500">*</span></label>
                        <input type="number" name="pagu" value="{{ old('pagu', $program->pagu) }}" placeholder="0" step="0.01" min="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                        @error('pagu')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Realisasi <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="number" name="realisasi" value="{{ old('realisasi', $program->realisasi) }}" placeholder="0" step="0.01" min="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('realisasi')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('program-kegiatan.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                    <button type="submit" class="px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
