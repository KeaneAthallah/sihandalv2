<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Pengaturan" :breadcrumbs="['Pengaturan']" />
    </x-slot>

    <div class="max-w-4xl space-y-5">

        {{-- Section: Umum --}}
        <x-card x-data="{ tahunAnggaran: '2026', mataUang: 'IDR' }">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10">
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 text-primary"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Umum</h3>
                        <p class="text-xs text-slate-400">Pengaturan dasar aplikasi dan konfigurasi sistem</p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-5">
                <div>
                    <x-input-label value="Nama Aplikasi" />
                    <div class="mt-1.5 relative">
                        <x-text-input type="text" value="Sihandal" disabled class="bg-slate-50/80 text-slate-500 cursor-not-allowed pr-20" />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-semibold uppercase tracking-wider text-slate-400 bg-slate-100 px-2 py-0.5 rounded">Tetap</span>
                    </div>
                    <p class="mt-1.5 text-xs text-slate-400">Nama aplikasi tidak dapat diubah.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label value="Tahun Anggaran" />
                        <select x-model="tahunAnggaran" class="mt-1.5 w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Mata Uang" />
                        <select x-model="mataUang" class="mt-1.5 w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
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
        <x-card x-data="{ email: true, sms: false, dashboard: true }">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50">
                        <x-heroicon-o-bell class="w-5 h-5 text-amber-500"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Notifikasi</h3>
                        <p class="text-xs text-slate-400">Atur cara Anda menerima notifikasi dari sistem</p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-3">
                @php
                    $notificationSettings = [
                        ['key' => 'email', 'title' => 'Notifikasi Email', 'desc' => 'Terima notifikasi melalui email yang terdaftar', 'icon' => 'envelope'],
                        ['key' => 'sms', 'title' => 'Notifikasi SMS', 'desc' => 'Terima notifikasi melalui pesan singkat', 'icon' => 'chat-bubble-left'],
                        ['key' => 'dashboard', 'title' => 'Notifikasi Dashboard', 'desc' => 'Tampilkan notifikasi langsung di halaman dashboard', 'icon' => 'rectangle-stack'],
                    ];
                @endphp
                @foreach($notificationSettings as $n)
                    <div class="flex items-center justify-between gap-4 p-4 bg-slate-50/80 rounded-xl border border-slate-100 hover:border-slate-200 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white border border-slate-200/60">
                                <x-dynamic-component :component="'heroicon-o-' . $n['icon']" class="w-4 h-4 text-slate-400"/>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-800">{{ $n['title'] }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $n['desc'] }}</p>
                            </div>
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
        <x-card x-data="{ twoFactor: false, sessionTimeout: '30', passwordPolicy: 'medium' }">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50">
                        <x-heroicon-o-shield-check class="w-5 h-5 text-emerald-500"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Keamanan</h3>
                        <p class="text-xs text-slate-400">Pengaturan keamanan akun dan autentikasi</p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-5">
                <div class="flex items-center justify-between gap-4 p-4 bg-slate-50/80 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white border border-slate-200/60">
                            <x-heroicon-o-key class="w-4 h-4 text-slate-400"/>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800">Autentikasi Dua Faktor (2FA)</p>
                            <p class="text-xs text-slate-400 mt-0.5">Tambahkan lapisan keamanan ekstra pada akun Anda</p>
                        </div>
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
                        <x-input-label value="Batas Waktu Sesi" />
                        <select x-model="sessionTimeout" class="mt-1.5 w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
                            <option value="15">15 menit</option>
                            <option value="30">30 menit</option>
                            <option value="60">60 menit</option>
                            <option value="120">120 menit</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Kebijakan Kata Sandi" />
                        <select x-model="passwordPolicy" class="mt-1.5 w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary transition-all">
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
        <x-card>
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50">
                        <x-heroicon-o-arrow-down-tray class="w-5 h-5 text-purple-500"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Backup</h3>
                        <p class="text-xs text-slate-400">Ekspor data dan kelola backup sistem</p>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-slate-50/80 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white border border-slate-200/60">
                            <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-slate-400"/>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Ekspor Data</p>
                            <p class="text-xs text-slate-400 mt-0.5">Unduh seluruh data dalam format CSV atau Excel</p>
                        </div>
                    </div>
                    <x-primary-button type="button">
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4 -ml-0.5 mr-1.5"/>
                        Ekspor
                    </x-primary-button>
                </div>
                <div class="flex items-center justify-between gap-3 p-4 bg-slate-50/80 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white border border-slate-200/60">
                            <x-heroicon-o-clock class="w-4 h-4 text-slate-400"/>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Backup Terakhir</p>
                            <p class="text-xs text-slate-400 mt-0.5">14 Juli 2026, 03:00 WITA</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-600/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Berhasil
                    </span>
                </div>
                <div class="flex items-center justify-between gap-3 p-4 bg-slate-50/80 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white border border-slate-200/60">
                            <x-heroicon-o-server-stack class="w-4 h-4 text-slate-400"/>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Ukuran Data</p>
                            <p class="text-xs text-slate-400 mt-0.5">Total ukuran database dan file lampiran</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-slate-700 tabular-nums">248.5 MB</span>
                </div>
            </div>

            <x-slot name="actions">
                <x-primary-button>Simpan</x-primary-button>
            </x-slot>
        </x-card>

    </div>
</x-app-layout>
