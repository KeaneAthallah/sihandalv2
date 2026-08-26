<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengaturan" :breadcrumbs="['Pengaturan']" />
    </x-slot>

    <div class="max-w-4xl space-y-4 lg:space-y-6">

        {{-- Section: Umum --}}
        <div class="bg-card rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ tahunAnggaran: '2026', mataUang: 'IDR' }">
            <div class="px-5 lg:px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 shrink-0 rounded-xl bg-primary/10 flex items-center justify-center">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-primary"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Umum</h3>
                        <p class="text-xs text-slate-400">Pengaturan dasar aplikasi dan konfigurasi sistem</p>
                    </div>
                </div>
            </div>
            <div class="px-5 lg:px-6 py-5 space-y-5">
                <div>
                    <x-input-label value="Nama Aplikasi" />
                    <input type="text" value="Sihandal" readonly class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 cursor-not-allowed"/>
                    <p class="mt-1 text-xs text-slate-400">Nama aplikasi tidak dapat diubah.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label value="Tahun Anggaran" />
                        <select x-model="tahunAnggaran" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Mata Uang" />
                        <select x-model="mataUang" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="IDR">Rupiah Indonesia (IDR)</option>
                            <option value="USD">US Dollar (USD)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="px-5 lg:px-6 py-4 border-t border-slate-100 flex justify-end">
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </div>

        {{-- Section: Notifikasi --}}
        <div class="bg-card rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ email: true, sms: false, dashboard: true }">
            <div class="px-5 lg:px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 shrink-0 rounded-xl bg-amber-50 flex items-center justify-center">
                        <x-heroicon-o-bell class="w-5 h-5 text-amber-500"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Notifikasi</h3>
                        <p class="text-xs text-slate-400">Atur cara Anda menerima notifikasi dari sistem</p>
                    </div>
                </div>
            </div>
            <div class="px-5 lg:px-6 py-5 space-y-4">
                @php
                    $notifications = [
                        ['key' => 'email', 'title' => 'Notifikasi Email', 'desc' => 'Terima notifikasi melalui email yang terdaftar'],
                        ['key' => 'sms', 'title' => 'Notifikasi SMS', 'desc' => 'Terima notifikasi melalui pesan singkat'],
                        ['key' => 'dashboard', 'title' => 'Notifikasi Dashboard', 'desc' => 'Tampilkan notifikasi langsung di halaman dashboard'],
                    ];
                @endphp
                @foreach($notifications as $n)
                    <div class="flex items-center justify-between gap-4 p-4 bg-slate-50 rounded-xl">
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
            <div class="px-5 lg:px-6 py-4 border-t border-slate-100 flex justify-end">
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </div>

        {{-- Section: Keamanan --}}
        <div class="bg-card rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ twoFactor: false, sessionTimeout: '30', passwordPolicy: 'medium' }">
            <div class="px-5 lg:px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 shrink-0 rounded-xl bg-emerald-50 flex items-center justify-center">
                        <x-heroicon-o-shield-check class="w-5 h-5 text-emerald-500"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Keamanan</h3>
                        <p class="text-xs text-slate-400">Pengaturan keamanan akun dan autentikasi</p>
                    </div>
                </div>
            </div>
            <div class="px-5 lg:px-6 py-5 space-y-5">
                <div class="flex items-center justify-between gap-4 p-4 bg-slate-50 rounded-xl">
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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label value="Batas Waktu Sesi (menit)" />
                        <select x-model="sessionTimeout" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="15">15 menit</option>
                            <option value="30">30 menit</option>
                            <option value="60">60 menit</option>
                            <option value="120">120 menit</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Kebijakan Kata Sandi" />
                        <select x-model="passwordPolicy" class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="low">Rendah (min. 6 karakter)</option>
                            <option value="medium">Sedang (min. 8 karakter + angka)</option>
                            <option value="high">Tinggi (min. 12 karakter + huruf besar + angka + simbol)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="px-5 lg:px-6 py-4 border-t border-slate-100 flex justify-end">
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </div>

        {{-- Section: Backup --}}
        <div class="bg-card rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 lg:px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 shrink-0 rounded-xl bg-purple-50 flex items-center justify-center">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-purple-500"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Backup</h3>
                        <p class="text-xs text-slate-400">Ekspor data dan kelola backup sistem</p>
                    </div>
                </div>
            </div>
            <div class="px-5 lg:px-6 py-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="text-sm font-medium text-slate-800">Ekspor Data</p>
                        <p class="text-xs text-slate-400 mt-0.5">Unduh seluruh data dalam format CSV atau Excel</p>
                    </div>
                    <button class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-sm">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4"/>
                        Ekspor Data
                    </button>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="text-sm font-medium text-slate-800">Backup Terakhir</p>
                        <p class="text-xs text-slate-400 mt-0.5">14 Juli 2026, 03:00 WITA &middot; Berhasil</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                        Berhasil
                    </span>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="text-sm font-medium text-slate-800">Ukuran Data</p>
                        <p class="text-xs text-slate-400 mt-0.5">Total ukuran database dan file lampiran</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-700">248.5 MB</span>
                </div>
            </div>
            <div class="px-5 lg:px-6 py-4 border-t border-slate-100 flex justify-end">
                <x-primary-button>Simpan</x-primary-button>
            </div>
        </div>

    </div>
</x-app-layout>
