<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Sub Kegiatan" :breadcrumbs="['Program & Kegiatan', $kegiatan->nama_kegiatan, 'Tambah Sub Kegiatan']">
            <x-slot name="actions">
                <x-secondary-button onclick="window.location='{{ route('sub-kegiatan.index', $kegiatan) }}'">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    Kembali
                </x-secondary-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('sub-kegiatan.store', $kegiatan) }}" method="POST">
            @csrf

            <x-card title="Data Sub Kegiatan">
                <div class="space-y-4">
                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm">
                        <span class="text-xs font-mono font-bold text-primary">{{ $kegiatan->kode_kegiatan }}</span>
                        <p class="text-slate-700 font-medium mt-0.5">{{ $kegiatan->nama_kegiatan }}</p>
                    </div>

                    <div>
                        <x-input-label for="kode_sub_kegiatan" value="Kode Sub Kegiatan" required/>
                        <x-text-input type="text" name="kode_sub_kegiatan" id="kode_sub_kegiatan" class="mt-1" placeholder="Contoh: 1.01.01" required/>
                        <x-input-error :messages="$errors->get('kode_sub_kegiatan')"/>
                    </div>

                    <div>
                        <x-input-label for="nama_sub_kegiatan" value="Nama Sub Kegiatan" required/>
                        <x-text-input type="text" name="nama_sub_kegiatan" id="nama_sub_kegiatan" class="mt-1" placeholder="Masukkan nama sub kegiatan" required/>
                        <x-input-error :messages="$errors->get('nama_sub_kegiatan')"/>
                    </div>

                    <div>
                        <x-input-label for="pagu" value="Pagu" required/>
                        <x-text-input type="number" name="pagu" id="pagu" class="mt-1" placeholder="Masukkan pagu anggaran" step="0.01" min="0" required/>
                        <x-input-error :messages="$errors->get('pagu')"/>
                    </div>
                </div>
            </x-card>

            <div class="mt-5 flex items-center justify-end gap-3">
                <x-secondary-button type="button" onclick="window.location='{{ route('sub-kegiatan.index', $kegiatan) }}'">Batal</x-secondary-button>
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>