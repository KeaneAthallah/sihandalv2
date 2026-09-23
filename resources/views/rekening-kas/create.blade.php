<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Rekening Kas" :breadcrumbs="['Rekening Kas', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card title="Form Tambah Rekening">
            <form action="{{ route('rekening-kas.store') }}" method="POST" x-data="{ tipe: '{{ old('tipe', '') }}' }">
                @csrf
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kode" value="Kode Rekening" />
                            <x-text-input type="text" name="kode" id="kode" value="{{ old('kode') }}" placeholder="Contoh: 1101" required class="mt-1.5" />
                            <x-input-error :messages="$errors->get('kode')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="tipe" value="Tipe Rekening" />
                            <select name="tipe" id="tipe" x-model="tipe" required class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
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
                        <x-text-input type="text" name="nama" id="nama" value="{{ old('nama') }}" placeholder="Contoh: Kas Besar" required class="mt-1.5" />
                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                    </div>

                    <div x-show="tipe === 'kas'" x-cloak class="rounded-lg bg-slate-50 border border-slate-200 p-4">
                        <x-input-label for="parent_id" value="Rekening Induk (Opsional)" />
                        <p class="mt-1 text-xs text-slate-400">Pilih rekening kas induk untuk menjadikan rekening ini sebagai rekening detail kas.</p>
                        <select name="parent_id" id="parent_id" class="mt-2 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Tanpa Induk (Rekening Kas Utama)</option>
                            @foreach($kasRekenings as $kas)
                                <option value="{{ $kas->id }}" {{ old('parent_id') == $kas->id ? 'selected' : '' }}>{{ $kas->kode }} - {{ $kas->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('parent_id')" class="mt-1" />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('rekening-kas.index') }}" class="btn-secondary">
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
