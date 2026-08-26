<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Penerimaan" :breadcrumbs="['Penerimaan', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Form Edit Penerimaan</h2>
            </div>
            <form action="{{ route('penerimaan.update', $penerimaan) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">OPD <span class="text-red-500">*</span></label>
                        <select name="opd_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id', $penerimaan->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        @error('opd_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Rekening <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <select name="rekening_id" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="">Pilih Rekening (Opsional)</option>
                            @foreach($rekenings as $rekening)
                                <option value="{{ $rekening->id }}" {{ old('rekening_id', $penerimaan->rekening_id) == $rekening->id ? 'selected' : '' }}>{{ $rekening->kode . ' - ' . $rekening->nama }}</option>
                            @endforeach
                        </select>
                        @error('rekening_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Sumber Dana <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="kode_sumber_dana" value="{{ old('kode_sumber_dana', $penerimaan->kode_sumber_dana) }}" placeholder="Opsional" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('kode_sumber_dana')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Sumber Dana <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="nama_sumber_dana" value="{{ old('nama_sumber_dana', $penerimaan->nama_sumber_dana) }}" placeholder="Opsional" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('nama_sumber_dana')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Target <span class="text-red-500">*</span></label>
                        <input type="number" name="target" value="{{ old('target', $penerimaan->target) }}" placeholder="0" step="0.01" min="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all" required>
                        @error('target')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Realisasi <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="number" name="realisasi" value="{{ old('realisasi', $penerimaan->realisasi) }}" placeholder="0" step="0.01" min="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('realisasi')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $penerimaan->tanggal?->format('Y-m-d')) }}" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('tanggal')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <textarea name="keterangan" rows="2" placeholder="Opsional" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">{{ old('keterangan', $penerimaan->keterangan) }}</textarea>
                        @error('keterangan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('penerimaan.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                    <button type="submit" class="px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
