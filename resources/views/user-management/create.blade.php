<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah User" :breadcrumbs="['Pengaturan', 'User Management', 'Tambah']" />
    </x-slot>

    @if($errors->any())
        <x-alert type="danger">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="max-w-2xl">
        <x-card title="Form Tambah User">
            <form action="{{ route('user-management.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <x-input-label value="Nama Lengkap" />
                        <x-text-input type="text" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" class="mt-1.5" required autofocus />
                        @error('name')
                            <x-input-error :messages="$message" class="mt-1.5" />
                        @enderror
                    </div>

                    <div>
                        <x-input-label value="Email" />
                        <x-text-input type="email" name="email" value="{{ old('email') }}" placeholder="user@email.com" class="mt-1.5" required />
                        @error('email')
                            <x-input-error :messages="$message" class="mt-1.5" />
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label value="Role" />
                            <select name="role" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" required>
                                <option value="opd" {{ old('role', 'opd') == 'opd' ? 'selected' : '' }}>OPD</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <x-input-error :messages="$message" class="mt-1.5" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label value="OPD" />
                            <select name="opd_id" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                                <option value="">Pilih OPD</option>
                                @foreach($opds as $opd)
                                    <option value="{{ $opd->id }}" {{ old('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                                @endforeach
                            </select>
                            @error('opd_id')
                                <x-input-error :messages="$message" class="mt-1.5" />
                            @enderror
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-4">Keamanan</p>
                    </div>

                    <div>
                        <x-input-label value="Password" />
                        <x-text-input type="password" name="password" placeholder="Minimal 8 karakter" class="mt-1.5" required />
                        @error('password')
                            <x-input-error :messages="$message" class="mt-1.5" />
                        @enderror
                    </div>

                    <div>
                        <x-input-label value="Konfirmasi Password" />
                        <x-text-input type="password" name="password_confirmation" placeholder="Ulangi password" class="mt-1.5" required />
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <a href="{{ route('user-management.index') }}">
                        <x-secondary-button type="button">Batal</x-secondary-button>
                    </a>
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
