@props(['status' => 'pending'])

@php
    $config = match($status) {
        'draft' => ['label' => 'Draft', 'classes' => 'bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300'],
        'menunggu', 'pending' => ['label' => 'Menunggu', 'classes' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'],
        'disetujui', 'approved' => ['label' => 'Disetujui', 'classes' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'],
        'ditolak', 'rejected' => ['label' => 'Ditolak', 'classes' => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400'],
        'realized' => ['label' => 'Direalisasi', 'classes' => 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400'],
        'verified' => ['label' => 'Terverifikasi', 'classes' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'],
        'diproses' => ['label' => 'Diproses', 'classes' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400'],
        'selesai' => ['label' => 'Selesai', 'classes' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'],
        'gagal' => ['label' => 'Gagal', 'classes' => 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400'],
        default => ['label' => $status, 'classes' => 'bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300'],
    };
@endphp

<span {{ $attributes->merge(['class' => 'badge ' . $config['classes']]) }}>
    <span class="badge-dot"></span>
    {{ $config['label'] }}
</span>
