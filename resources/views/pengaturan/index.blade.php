<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengaturan" :breadcrumbs="['Pengaturan']" />
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Keuangan --}}
        <x-card title="Keuangan">
            <form method="POST" action="{{ route('pengaturan.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Kuota Penerimaan OPD (%)" />
                        <x-text-input type="number" name="penerimaan_kuota_persen"
                            :value="old('penerimaan_kuota_persen', $kuotaPenerimaanPersen)"
                            min="0" max="100" step="0.01" required/>
                        <x-input-error :messages="$errors->get('penerimaan_kuota_persen')" class="mt-1"/>
                        <p class="mt-1 text-xs text-slate-400">
                            Persentase penerimaan yang dapat dipakai OPD. Contoh: 10% dari Rp 1.000.000 = Rp 100.000.
                            Admin selalu melihat kas penuh tanpa potongan kuota.
                        </p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <x-primary-button>Simpan</x-primary-button>
                </div>
            </form>
        </x-card>

        {{-- Sistem --}}
        <x-card title="Sistem">
            <div class="space-y-6">
                {{-- Notifikasi Realisasi --}}
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <h4 class="font-medium text-sm text-slate-800">Kirim Notifikasi Realisasi</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Kirim notifikasi email saat anggaran mencapai target realisasi</p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex items-center justify-end">
                    <button class="btn-secondary !py-1.5 !px-3 text-xs">Simpan Perubahan</button>
                </div>

                <hr class="border-slate-100">

                {{-- Batas Throttle --}}
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium text-sm text-slate-800">Batas Throttle</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Maksimum percobaan login sebelum akun dikunci sementara</p>
                    </div>
                    <x-text-input type="number" value="10" min="1" max="100" class="!w-24"/>
                </div>

                <div class="flex items-center justify-end">
                    <button class="btn-secondary !py-1.5 !px-3 text-xs">Simpan Perubahan</button>
                </div>
            </div>
        </x-card>

        {{-- Keamanan --}}
        <x-card title="Keamanan">
            <div class="space-y-6">
                {{-- 2FA --}}
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <h4 class="font-medium text-sm text-slate-800">Two-Factor Authentication</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Wajibkan 2FA untuk semua pengguna admin</p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="relative w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                <div class="flex items-center justify-end">
                    <button class="btn-secondary !py-1.5 !px-3 text-xs">Simpan Perubahan</button>
                </div>

                <hr class="border-slate-100">

                {{-- Sesi --}}
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium text-sm text-slate-800">Sesi</h4>
                        <p class="text-xs text-slate-400 mt-0.5">Durasi sesi pengguna sebelum diminta login kembali</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-text-input type="number" value="120" min="5" max="480" class="!w-24"/>
                        <span class="text-xs text-slate-400">menit</span>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button class="btn-secondary !py-1.5 !px-3 text-xs">Simpan Perubahan</button>
                </div>
            </div>
        </x-card>

    </div>
</x-app-layout>
