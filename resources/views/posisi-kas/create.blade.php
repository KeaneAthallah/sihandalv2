<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Posisi Kas" :breadcrumbs="['Posisi Kas', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Posisi Kas">
            <form action="{{ route('posisi-kas.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="opd_id" value="OPD" />
                        <select name="opd_id" id="opd_id" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')" />
                    </div>
                    <div>
                        <x-input-label for="rekening_id" value="Rekening" />
                        <select name="rekening_id" id="rekening_id" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Pilih Rekening</option>
                            @foreach($rekenings as $rek)
                                <option value="{{ $rek->id }}" {{ old('rekening_id') == $rek->id ? 'selected' : '' }}>{{ $rek->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rekening_id')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="tanggal" value="Tanggal" />
                    <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    <x-input-error :messages="$errors->get('tanggal')" />
                </div>

                <div>
                    <x-input-label for="saldo_awal" value="Saldo Awal" />
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                        <input type="number" name="saldo_awal" id="saldo_awal" step="0.01" min="0" value="{{ old('saldo_awal') }}" placeholder="0" required class="w-full pl-10 pr-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                    </div>
                    <x-input-error :messages="$errors->get('saldo_awal')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="penerimaan" value="Penerimaan" />
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-emerald-500 font-medium">+ Rp</span>
                            <input type="number" name="penerimaan" id="penerimaan" step="0.01" min="0" value="{{ old('penerimaan') }}" placeholder="0" class="w-full pl-[4.5rem] pr-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        </div>
                        <x-input-error :messages="$errors->get('penerimaan')" />
                    </div>
                    <div>
                        <x-input-label for="pengeluaran" value="Pengeluaran" />
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-red-500 font-medium">- Rp</span>
                            <input type="number" name="pengeluaran" id="pengeluaran" step="0.01" min="0" value="{{ old('pengeluaran') }}" placeholder="0" class="w-full pl-[4.5rem] pr-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                        </div>
                        <x-input-error :messages="$errors->get('pengeluaran')" />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('posisi-kas.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
