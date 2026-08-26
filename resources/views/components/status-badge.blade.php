@props(['status' => 'pending'])

@php
    $config = match($status) {
        'draft' => ['label' => 'Draft', 'classes' => 'bg-slate-50 text-slate-700 border-slate-200'],
        'menunggu', 'pending' => ['label' => 'Menunggu', 'classes' => 'bg-amber-50 text-amber-700 border-amber-200'],
        'disetujui', 'approved' => ['label' => 'Disetujui', 'classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'ditolak', 'rejected' => ['label' => 'Ditolak', 'classes' => 'bg-red-50 text-red-700 border-red-200'],
        'realized' => ['label' => 'Direalisasi', 'classes' => 'bg-purple-50 text-purple-700 border-purple-200'],
        'verified' => ['label' => 'Terverifikasi', 'classes' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'diproses' => ['label' => 'Diproses', 'classes' => 'bg-amber-50 text-amber-700 border-amber-200'],
        'selesai' => ['label' => 'Selesai', 'classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'gagal' => ['label' => 'Gagal', 'classes' => 'bg-red-50 text-red-700 border-red-200'],
        default => ['label' => $status, 'classes' => 'bg-slate-50 text-slate-700 border-slate-200'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold border whitespace-nowrap ' . $config['classes']]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
    {{ $config['label'] }}
</span>
