@props(['status' => 'pending'])

@php
    $config = match($status) {
        'draft' => ['label' => 'Draft', 'classes' => 'bg-slate-100 text-slate-600'],
        'menunggu', 'pending' => ['label' => 'Menunggu', 'classes' => 'bg-amber-50 text-amber-700'],
        'disetujui', 'approved' => ['label' => 'Disetujui', 'classes' => 'bg-emerald-50 text-emerald-700'],
        'ditolak', 'rejected' => ['label' => 'Ditolak', 'classes' => 'bg-red-50 text-red-700'],
        'realized' => ['label' => 'Direalisasi', 'classes' => 'bg-purple-50 text-purple-700'],
        'verified' => ['label' => 'Terverifikasi', 'classes' => 'bg-blue-50 text-blue-700'],
        'diproses' => ['label' => 'Diproses', 'classes' => 'bg-amber-50 text-amber-700'],
        'selesai' => ['label' => 'Selesai', 'classes' => 'bg-emerald-50 text-emerald-700'],
        'gagal' => ['label' => 'Gagal', 'classes' => 'bg-red-50 text-red-700'],
        default => ['label' => $status, 'classes' => 'bg-slate-100 text-slate-600'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium whitespace-nowrap ' . $config['classes']]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
    {{ $config['label'] }}
</span>
