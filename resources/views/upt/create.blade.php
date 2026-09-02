<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah UPT" :breadcrumbs="['Data Master', 'UPT', 'Tambah']">
            <x-slot name="actions">
                <x-secondary-button onclick="window.location='{{ route('upt.index') }}'">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    Kembali
                </x-secondary-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('upt.store') }}" method="POST">
            @csrf

            <x-card title="Data UPT">
                <div class="space-y-4">
                    @if(auth()->user()->isAdmin())
                        <div>
                            <x-input-label for="opd_id" value="OPD" required/>
                            <select name="opd_id" id="opd_id" required class="mt-1 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">-- Pilih OPD --</option>
                                @foreach($opds as $opd)
                                    <option value="{{ $opd->id }}" @selected(old('opd_id') == $opd->id)>{{ $opd->nama }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('opd_id')"/>
                        </div>
                    @else
                        <input type="hidden" name="opd_id" value="{{ auth()->user()->opd_id }}"/>
                    @endif

                    <div>
                        <x-input-label for="kode" value="Kode UPT" required/>
                        <x-text-input type="text" name="kode" id="kode" class="mt-1" placeholder="Contoh: 001" required/>
                        <x-input-error :messages="$errors->get('kode')"/>
                    </div>

                    <div>
                        <x-input-label for="nama" value="Nama UPT" required/>
                        <x-text-input type="text" name="nama" id="nama" class="mt-1" placeholder="Masukkan nama UPT" required/>
                        <x-input-error :messages="$errors->get('nama')"/>
                    </div>
                </div>
            </x-card>

            <div class="mt-5 flex items-center justify-end gap-3">
                <x-secondary-button type="button" onclick="window.location='{{ route('upt.index') }}'">Batal</x-secondary-button>
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>