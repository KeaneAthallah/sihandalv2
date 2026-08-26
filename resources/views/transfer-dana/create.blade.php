<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Transfer Dana" :breadcrumbs="['Transfer Dana', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Form Tambah Transfer Dana</h2>
            </div>
            <form action="{{ route('transfer-dana.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label for="opd_id" class="block text-sm font-medium text-slate-700 mb-1.5">OPD <span class="text-red-500">*</span></label>
                        <select name="opd_id" id="opd_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        @error('opd_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah <span class="text-red-500">*</span></label>
                        <input type="number" name="jumlah" id="jumlah" min="0" value="{{ old('jumlah') }}" placeholder="0" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('jumlah')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sumber_dana" class="block text-sm font-medium text-slate-700 mb-1.5">Sumber Dana <span class="text-red-500">*</span></label>
                        <input type="text" name="sumber_dana" id="sumber_dana" value="{{ old('sumber_dana') }}" placeholder="Masukkan sumber dana" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('sumber_dana')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('tanggal')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <textarea name="keterangan" id="keterangan" rows="3" placeholder="Masukkan keterangan (opsional)" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('transfer-dana.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                    <button type="submit" class="px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
