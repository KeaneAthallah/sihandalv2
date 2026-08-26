<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit Rekening Kas" :breadcrumbs="['Rekening Kas', 'Edit']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            <form action="{{ route('rekening-kas.update', $rekening) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="kode" value="Kode Rekening" />
                            <x-text-input type="text" name="kode" id="kode" value="{{ old('kode', $rekening->kode) }}" placeholder="Contoh: 1101" required class="mt-1.5" />
                            <x-input-error :messages="$errors->get('kode')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="tipe" value="Tipe Rekening" />
                            <select name="tipe" id="tipe" required class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                                <option value="">Pilih tipe...</option>
                                <option value="kas" {{ old('tipe', $rekening->tipe) === 'kas' ? 'selected' : '' }}>Kas</option>
                                <option value="non-kas" {{ old('tipe', $rekening->tipe) === 'non-kas' ? 'selected' : '' }}>Non-Kas</option>
                                <option value="pendapatan" {{ old('tipe', $rekening->tipe) === 'pendapatan' ? 'selected' : '' }}>Pendapatan</option>
                                <option value="belanja" {{ old('tipe', $rekening->tipe) === 'belanja' ? 'selected' : '' }}>Belanja</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipe')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="nama" value="Nama Rekening" />
                        <x-text-input type="text" name="nama" id="nama" value="{{ old('nama', $rekening->nama) }}" placeholder="Contoh: Kas Besar" required class="mt-1.5" />
                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="saldo" value="Saldo Awal" />
                        <x-text-input type="number" name="saldo" id="saldo" step="0.01" value="{{ old('saldo', $rekening->saldo) }}" placeholder="0" class="mt-1.5" />
                        <p class="text-xs text-slate-400 mt-1.5">Masukkan saldo awal rekening. Dapat dikosongkan atau diubah nanti.</p>
                        <x-input-error :messages="$errors->get('saldo')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                    <x-secondary-button type="button" onclick="window.location='{{ route('rekening-kas.index') }}'">
                        Batal
                    </x-secondary-button>
                    <x-primary-button>
                        Simpan Perubahan
                    </x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
