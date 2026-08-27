<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Program" :breadcrumbs="['Program & Kegiatan', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <form action="{{ route('program-kegiatan.store') }}" method="POST" class="space-y-5">
            @csrf

            <x-card title="Program">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kode_program" value="Kode Program" />
                            <x-text-input type="text" name="kode_program" id="kode_program" value="{{ old('kode_program') }}" placeholder="Misal: 1.1" required class="mt-1.5" />
                            <x-input-error :messages="$errors->get('kode_program')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="nama_program" value="Nama Program" />
                            <x-text-input type="text" name="nama_program" id="nama_program" value="{{ old('nama_program') }}" placeholder="Masukkan nama program" required class="mt-1.5" />
                            <x-input-error :messages="$errors->get('nama_program')" class="mt-1" />
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Setelah program dibuat, kegiatan dapat ditambahkan dari halaman edit program.</p>
                </div>
            </x-card>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('program-kegiatan.index') }}" class="btn-secondary">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
