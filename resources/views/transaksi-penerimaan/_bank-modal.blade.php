<div x-show="showBankModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/50 transition-opacity" @click="showBankModal = false"></div>

        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-800">Tambah Rekening Bank</h3>
                    <p class="mt-1 text-xs text-slate-400">Rekening bank baru langsung tersedia pada pilihan di atas.</p>
                </div>
                <button type="button" @click="showBankModal = false" class="icon-btn hover:bg-slate-50" title="Tutup">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <div>
                    <x-input-label value="Nama Bank" />
                    <input type="text" x-model="bankForm.bank_name" placeholder="Contoh: Bank BRI"
                        class="mt-1.5 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" />
                    <template x-if="bankErrors.bank_name">
                        <p class="mt-1 text-xs text-red-600" x-text="bankErrors.bank_name[0]"></p>
                    </template>
                </div>
                <div>
                    <x-input-label value="Nomor Rekening" />
                    <input type="text" x-model="bankForm.account_number" placeholder="Contoh: 0010-01-000123-7"
                        class="mt-1.5 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" />
                    <template x-if="bankErrors.account_number">
                        <p class="mt-1 text-xs text-red-600" x-text="bankErrors.account_number[0]"></p>
                    </template>
                </div>
                <div>
                    <x-input-label value="Atas Nama" />
                    <input type="text" x-model="bankForm.account_name" placeholder="Contoh: Dinas Kesehatan"
                        class="mt-1.5 w-full px-3 py-2 bg-white border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" />
                    <template x-if="bankErrors.account_name">
                        <p class="mt-1 text-xs text-red-600" x-text="bankErrors.account_name[0]"></p>
                    </template>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-end gap-3">
                <button type="button" @click="showBankModal = false" class="btn-secondary">Batal</button>
                <button type="button" @click="saveBank()" :disabled="savingBank" class="btn-primary"
                    x-text="savingBank ? 'Menyimpan...' : 'Simpan Bank'"></button>
            </div>
        </div>
    </div>
</div>