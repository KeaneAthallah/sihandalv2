<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Sumber Dana" :breadcrumbs="['Sumber Dana', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Form Edit Sumber Dana</h2>
            </div>
            <form action="{{ route('sumber-dana.update', $sumberDana) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label for="opd_id" class="block text-sm font-medium text-slate-700 mb-1.5">OPD <span class="text-red-500">*</span></label>
                        <select name="opd_id" id="opd_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id', $sumberDana->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        @error('opd_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nama_sumber_dana" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Sumber Dana <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_sumber_dana" id="nama_sumber_dana" value="{{ old('nama_sumber_dana', $sumberDana->nama_sumber_dana) }}" placeholder="Masukkan nama sumber dana" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('nama_sumber_dana')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pagu" class="block text-sm font-medium text-slate-700 mb-1.5">Pagu <span class="text-red-500">*</span></label>
                        <input type="number" name="pagu" id="pagu" step="0.01" min="0" value="{{ old('pagu', $sumberDana->pagu) }}" placeholder="Masukkan pagu" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('pagu')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="realisasi" class="block text-sm font-medium text-slate-700 mb-1.5">Realisasi <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="number" name="realisasi" id="realisasi" step="0.01" min="0" value="{{ old('realisasi', $sumberDana->realisasi) }}" placeholder="Masukkan realisasi" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('realisasi')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('sumber-dana.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                    <button type="submit" class="px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
