<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Posisi Kas" :breadcrumbs="['Posisi Kas', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Form Edit Posisi Kas</h2>
            </div>
            <form action="{{ route('posisi-kas.update', $posisiKas) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label for="opd_id" class="block text-sm font-medium text-slate-700 mb-1.5">OPD <span class="text-red-500">*</span></label>
                        <select name="opd_id" id="opd_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id', $posisiKas->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        @error('opd_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="rekening_id" class="block text-sm font-medium text-slate-700 mb-1.5">Rekening <span class="text-red-500">*</span></label>
                        <select name="rekening_id" id="rekening_id" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="">Pilih Rekening</option>
                            @foreach($rekenings as $rek)
                                <option value="{{ $rek->id }}" {{ old('rekening_id', $posisiKas->rekening_id) == $rek->id ? 'selected' : '' }}>{{ $rek->nama }}</option>
                            @endforeach
                        </select>
                        @error('rekening_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $posisiKas->tanggal?->format('Y-m-d')) }}" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('tanggal')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="saldo_awal" class="block text-sm font-medium text-slate-700 mb-1.5">Saldo Awal <span class="text-red-500">*</span></label>
                        <input type="number" name="saldo_awal" id="saldo_awal" step="0.01" min="0" value="{{ old('saldo_awal', $posisiKas->saldo_awal) }}" placeholder="0" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('saldo_awal')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="penerimaan" class="block text-sm font-medium text-slate-700 mb-1.5">Penerimaan <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="number" name="penerimaan" id="penerimaan" step="0.01" min="0" value="{{ old('penerimaan', $posisiKas->penerimaan) }}" placeholder="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('penerimaan')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="pengeluaran" class="block text-sm font-medium text-slate-700 mb-1.5">Pengeluaran <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="number" name="pengeluaran" id="pengeluaran" step="0.01" min="0" value="{{ old('pengeluaran', $posisiKas->pengeluaran) }}" placeholder="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('pengeluaran')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('posisi-kas.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                    <button type="submit" class="px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
