<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Tambah Rekening Kas" :breadcrumbs="['Rekening Kas', 'Tambah']" />
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-semibold text-slate-800">Form Tambah Rekening Kas</h2>
            </div>
            <form action="{{ route('rekening-kas.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label for="kode" class="block text-sm font-medium text-slate-700 mb-1.5">Kode <span class="text-red-500">*</span></label>
                        <input type="text" name="kode" id="kode" placeholder="Contoh: 1101" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('kode')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nama" class="block text-sm font-medium text-slate-700 mb-1.5">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" id="nama" placeholder="Contoh: Kas Besar" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('nama')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tipe" class="block text-sm font-medium text-slate-700 mb-1.5">Tipe <span class="text-red-500">*</span></label>
                        <select name="tipe" id="tipe" required class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                            <option value="">Pilih tipe...</option>
                            <option value="kas">Kas</option>
                            <option value="non-kas">Non-Kas</option>
                            <option value="pendapatan">Pendapatan</option>
                            <option value="belanja">Belanja</option>
                        </select>
                        @error('tipe')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="saldo" class="block text-sm font-medium text-slate-700 mb-1.5">Saldo <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="number" name="saldo" id="saldo" step="0.01" value="{{ old('saldo', 0) }}" placeholder="0" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all">
                        @error('saldo')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('rekening-kas.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition-all">Batal</a>
                    <button type="submit" class="px-4 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
