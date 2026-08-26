<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Posisi Kas" :breadcrumbs="['Posisi Kas', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <div class="mb-6 pb-5 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Form Edit Posisi Kas</h2>
                <p class="text-sm text-slate-400 mt-0.5">Ubah data posisi kas sesuai kebutuhan</p>
            </div>

            <form action="{{ route('posisi-kas.update', $posisiKas) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="opd_id" value="OPD" />
                        <select name="opd_id" id="opd_id" required class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->id }}" {{ old('opd_id', $posisiKas->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('opd_id')" class="mt-1.5" />
                    </div>
                    <div>
                        <x-input-label for="rekening_id" value="Rekening" />
                        <select name="rekening_id" id="rekening_id" required class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                            <option value="">Pilih Rekening</option>
                            @foreach($rekenings as $rek)
                                <option value="{{ $rek->id }}" {{ old('rekening_id', $posisiKas->rekening_id) == $rek->id ? 'selected' : '' }}>{{ $rek->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('rekening_id')" class="mt-1.5" />
                    </div>
                </div>

                <div>
                    <x-input-label for="tanggal" value="Tanggal" />
                    <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $posisiKas->tanggal?->format('Y-m-d')) }}" class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                    <x-input-error :messages="$errors->get('tanggal')" class="mt-1.5" />
                </div>

                {{-- Financial Fields --}}
                <div class="bg-slate-50/80 border border-slate-200/60 rounded-xl p-5 space-y-4">
                    <h3 class="text-sm font-semibold text-slate-700">Rincian Keuangan</h3>

                    <div>
                        <x-input-label for="saldo_awal" value="Saldo Awal *" />
                        <div class="relative mt-1.5">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-medium">Rp</span>
                            <input type="number" name="saldo_awal" id="saldo_awal" step="0.01" min="0" value="{{ old('saldo_awal', $posisiKas->saldo_awal) }}" placeholder="0" required class="w-full pl-12 pr-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                        </div>
                        <x-input-error :messages="$errors->get('saldo_awal')" class="mt-1.5" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="penerimaan" value="Penerimaan" />
                            <div class="relative mt-1.5">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-emerald-500 font-medium">+ Rp</span>
                                <input type="number" name="penerimaan" id="penerimaan" step="0.01" min="0" value="{{ old('penerimaan', $posisiKas->penerimaan) }}" placeholder="0" class="w-full pl-[4.5rem] pr-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                            </div>
                            <x-input-error :messages="$errors->get('penerimaan')" class="mt-1.5" />
                        </div>
                        <div>
                            <x-input-label for="pengeluaran" value="Pengeluaran" />
                            <div class="relative mt-1.5">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-red-500 font-medium">- Rp</span>
                                <input type="number" name="pengeluaran" id="pengeluaran" step="0.01" min="0" value="{{ old('pengeluaran', $posisiKas->pengeluaran) }}" placeholder="0" class="w-full pl-[4.5rem] pr-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                            </div>
                            <x-input-error :messages="$errors->get('pengeluaran')" class="mt-1.5" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                    <a href="{{ route('posisi-kas.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-xl font-semibold text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-all">
                        Batal
                    </a>
                    <x-primary-button>
                        <x-heroicon-o-check class="w-4 h-4 mr-1.5"/>
                        Simpan Perubahan
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
