<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Rekening Kas" :breadcrumbs="['Rekening Kas', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Tambah Rekening">
            <form action="{{ route('rekening-kas.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kode" value="Kode Rekening" />
                            <x-text-input type="text" name="kode" id="kode" placeholder="Contoh: 1101" required class="mt-1.5" />
                            <x-input-error :messages="$errors->get('kode')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="tipe" value="Tipe Rekening" />
                            <select name="tipe" id="tipe" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih tipe...</option>
                                <option value="kas">Kas</option>
                                <option value="non-kas">Non-Kas</option>
                                <option value="pendapatan">Pendapatan</option>
                                <option value="belanja">Belanja</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipe')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="nama" value="Nama Rekening" />
                        <x-text-input type="text" name="nama" id="nama" placeholder="Contoh: Kas Besar" required class="mt-1.5" />
                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="saldo" value="Saldo Awal" />
                        <x-text-input type="number" name="saldo" id="saldo" step="0.01" value="{{ old('saldo', 0) }}" placeholder="0" class="mt-1.5" />
                        <p class="text-xs text-slate-400 mt-1.5">Masukkan saldo awal rekening. Dapat dikosongkan atau diubah nanti.</p>
                        <x-input-error :messages="$errors->get('saldo')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('rekening-kas.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
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
