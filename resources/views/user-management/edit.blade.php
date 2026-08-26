<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Edit User" :breadcrumbs="['Pengaturan', 'User Management', 'Edit']" />
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

    <div class="max-w-2xl mx-auto">
        <x-card>
            <form action="{{ route('user-management.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-5">
                    <div>
                        <x-input-label value="Nama Lengkap" />
                        <x-text-input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Masukkan nama lengkap" class="mt-1.5" required autofocus />
                        @error('name')
                            <x-input-error :messages="$message" class="mt-1.5" />
                        @enderror
                    </div>

                    <div>
                        <x-input-label value="Email" />
                        <x-text-input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="user@email.com" class="mt-1.5" required />
                        @error('email')
                            <x-input-error :messages="$message" class="mt-1.5" />
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label value="Role" />
                            <select name="role" class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all" required>
                                <option value="opd" {{ old('role', $user->role) == 'opd' ? 'selected' : '' }}>OPD</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <x-input-error :messages="$message" class="mt-1.5" />
                            @enderror
                        </div>

                        <div>
                            <x-input-label value="OPD" />
                            <select name="opd_id" class="mt-1.5 w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                                <option value="">Pilih OPD</option>
                                @foreach($opds as $opd)
                                    <option value="{{ $opd->id }}" {{ old('opd_id', $user->opd_id) == $opd->id ? 'selected' : '' }}>{{ $opd->nama }}</option>
                                @endforeach
                            </select>
                            @error('opd_id')
                                <x-input-error :messages="$message" class="mt-1.5" />
                            @enderror
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-4">Ubah Password</p>
                    </div>

                    <div>
                        <x-input-label value="Password Baru" />
                        <x-text-input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="mt-1.5" />
                        @error('password')
                            <x-input-error :messages="$message" class="mt-1.5" />
                        @enderror
                    </div>

                    <div>
                        <x-input-label value="Konfirmasi Password Baru" />
                        <x-text-input type="password" name="password_confirmation" placeholder="Ulangi password baru" class="mt-1.5" />
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                    <a href="{{ route('user-management.index') }}">
                        <x-secondary-button type="button">Batal</x-secondary-button>
                    </a>
                    <x-primary-button>Simpan Perubahan</x-primary-button>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
