<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengaturan" :breadcrumbs="['Pengaturan']" />
    </x-slot>

    <div class="max-w-2xl space-y-5">

        {{-- Section: Umum --}}
        <x-card title="Umum" x-data="{ tahunAnggaran: '2026', mataUang: 'IDR' }">
            <p class="text-xs text-slate-400 mb-4">Pengaturan dasar aplikasi dan konfigurasi sistem</p>
            <div class="space-y-4">
                <div>
                    <x-input-label value="Nama Aplikasi" />
                    <div class="mt-1.5 relative">
                        <x-text-input type="text" value="Sihandal" disabled class="bg-slate-50 text-slate-500 cursor-not-allowed pr-20" />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-semibold uppercase tracking-wide text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Tetap</span>
                    </div>
                    <p class="mt-1.5 text-xs text-slate-400">Nama aplikasi tidak dapat diubah.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Tahun Anggaran" />
                        <select x-model="tahunAnggaran" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Mata Uang" />
                        <select x-model="mataUang" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="IDR">Rupiah Indonesia (IDR)</option>
                            <option value="USD">US Dollar (USD)</option>
                        </select>
                    </div>
                </div>
            </div>
            <x-slot name="actions">
                <x-primary-button>Simpan</x-primary-button>
            </x-slot>
        </x-card>

        {{-- Section: Notifikasi --}}
        <x-card title="Notifikasi" x-data="{ email: true, sms: false, dashboard: true }">
            <p class="text-xs text-slate-400 mb-4">Atur cara Anda menerima notifikasi dari sistem</p>
            <div class="space-y-3">
                @php
                    $notificationSettings = [
                        ['key' => 'email', 'title' => 'Notifikasi Email', 'desc' => 'Terima notifikasi melalui email yang terdaftar'],
                        ['key' => 'sms', 'title' => 'Notifikasi SMS', 'desc' => 'Terima notifikasi melalui pesan singkat'],
                        ['key' => 'dashboard', 'title' => 'Notifikasi Dashboard', 'desc' => 'Tampilkan notifikasi langsung di halaman dashboard'],
                    ];
                @endphp
                @foreach($notificationSettings as $n)
                    <div class="flex items-center justify-between gap-4 p-4 rounded-lg border border-slate-200 hover:border-slate-300 transition-colors">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800">{{ $n['title'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $n['desc'] }}</p>
                        </div>
                        <button
                            @click="{{ $n['key'] }} = !{{ $n['key'] }}"
                            :class="{{ $n['key'] }} ? 'bg-primary' : 'bg-slate-200'"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2">
                            <span
                                :class="{{ $n['key'] }} ? 'translate-x-5' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                    </div>
                @endforeach
            </div>
            <x-slot name="actions">
                <x-primary-button>Simpan</x-primary-button>
            </x-slot>
        </x-card>

        {{-- Section: Keamanan --}}
        <x-card title="Keamanan" x-data="{ twoFactor: false, sessionTimeout: '30', passwordPolicy: 'medium' }">
            <p class="text-xs text-slate-400 mb-4">Pengaturan keamanan akun dan autentikasi</p>
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-4 p-4 rounded-lg border border-slate-200">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800">Autentikasi Dua Faktor (2FA)</p>
                        <p class="text-xs text-slate-400 mt-0.5">Tambahkan lapisan keamanan ekstra pada akun Anda</p>
                    </div>
                    <button
                        @click="twoFactor = !twoFactor"
                        :class="twoFactor ? 'bg-primary' : 'bg-slate-200'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary/20 focus:ring-offset-2">
                        <span
                            :class="twoFactor ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Batas Waktu Sesi" />
                        <select x-model="sessionTimeout" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="15">15 menit</option>
                            <option value="30">30 menit</option>
                            <option value="60">60 menit</option>
                            <option value="120">120 menit</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Kebijakan Kata Sandi" />
                        <select x-model="passwordPolicy" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="low">Rendah (min. 6 karakter)</option>
                            <option value="medium">Sedang (min. 8 karakter + angka)</option>
                            <option value="high">Tinggi (min. 12 karakter + huruf besar + angka + simbol)</option>
                        </select>
                    </div>
                </div>
            </div>
            <x-slot name="actions">
                <x-primary-button>Simpan</x-primary-button>
            </x-slot>
        </x-card>

        {{-- Section: Backup --}}
        <x-card title="Backup">
            <p class="text-xs text-slate-400 mb-4">Ekspor data dan kelola backup sistem</p>
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-lg border border-slate-200">
                    <div>
                        <p class="text-sm font-medium text-slate-800">Ekspor Data</p>
                        <p class="text-xs text-slate-400 mt-0.5">Unduh seluruh data dalam format CSV atau Excel</p>
                    </div>
                    <x-primary-button type="button">Ekspor</x-primary-button>
                </div>
                <div class="flex items-center justify-between gap-3 p-4 rounded-lg border border-slate-200">
                    <div>
                        <p class="text-sm font-medium text-slate-800">Backup Terakhir</p>
                        <p class="text-xs text-slate-400 mt-0.5">14 Juli 2026, 03:00 WITA</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-600/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Berhasil
                    </span>
                </div>
                <div class="flex items-center justify-between gap-3 p-4 rounded-lg border border-slate-200">
                    <div>
                        <p class="text-sm font-medium text-slate-800">Ukuran Data</p>
                        <p class="text-xs text-slate-400 mt-0.5">Total ukuran database dan file lampiran</p>
                    </div>
                    <span class="text-sm font-bold text-slate-700 tabular-nums">248.5 MB</span>
                </div>
            </div>
        </x-card>

    </div>
</x-app-layout>
