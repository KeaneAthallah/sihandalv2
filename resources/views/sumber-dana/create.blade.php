<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Sumber Dana" :breadcrumbs="['Data Master', 'Sumber Dana', 'Tambah']">
            <x-slot name="actions">
                <x-secondary-button onclick="window.location='{{ route('sumber-dana.index') }}'">
                    <x-heroicon-o-arrow-left class="w-4 h-4"/>
                    Kembali
                </x-secondary-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('sumber-dana.store') }}" method="POST">
            @csrf

            <x-card title="Data Sumber Dana">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="nama_sumber_dana" value="Nama Sumber Dana" required/>
                        <x-text-input type="text" name="nama_sumber_dana" id="nama_sumber_dana" class="mt-1" placeholder="Masukkan nama sumber dana" required/>
                        <x-input-error :messages="$errors->get('nama_sumber_dana')"/>
                    </div>
                </div>
            </x-card>

            <div class="mt-5 flex items-center justify-end gap-3">
                <x-secondary-button type="button" onclick="window.location='{{ route('sumber-dana.index') }}'">Batal</x-secondary-button>
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
