@props([
    'permintaanDana' => null,
    'nomor' => null,
    'opd' => null,
    'sumberDana' => null,
    'keperluan' => null,
    'jumlah' => 0,
    'status' => 'draft',
    'tanggal' => null,
    'created' => null,
    'currentStep' => 1,
])

@php
    $nomor = $nomor ?? $permintaanDana->nomor_permintaan ?? '-';
    $opd = $opd ?? $permintaanDana->opd->nama ?? '-';
    $sumberDana = $sumberDana ?? $permintaanDana->sumber_dana ?? '-';
    $keperluan = $keperluan ?? $permintaanDana->keperluan ?? '-';
    $jumlah = is_numeric($jumlah) ? $jumlah : ($permintaanDana->jumlah ?? 0);
    $status = $status ?? ($permintaanDana->status ?? 'draft');
    $tanggal = $tanggal ?? ($permintaanDana->tanggal ? $permintaanDana->tanggal->format('d M Y') : '-');
    $created = $created ?? ($permintaanDana?->created_at?->format('d M Y H:i') ?? '-');

    $statusConfig = match($status) {
        'draft' => ['label' => 'Draft', 'desc' => 'Permintaan belum diajukan', 'bg' => 'bg-surface', 'border' => 'border-border', 'text' => 'text-content-secondary', 'dot' => 'bg-slate-400'],
        'menunggu' => ['label' => 'Menunggu', 'desc' => 'Menunggu persetujuan', 'bg' => 'bg-amber-50 dark:bg-amber-500/10', 'border' => 'border-amber-200 dark:border-amber-500/30', 'text' => 'text-amber-700 dark:text-amber-400', 'dot' => 'bg-amber-500'],
        'disetujui' => ['label' => 'Disetujui', 'desc' => 'Permintaan telah disetujui', 'bg' => 'bg-emerald-50 dark:bg-emerald-500/10', 'border' => 'border-emerald-200 dark:border-emerald-500/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500'],
        'ditolak' => ['label' => 'Ditolak', 'desc' => 'Permintaan ditolak', 'bg' => 'bg-red-50 dark:bg-red-500/10', 'border' => 'border-red-200 dark:border-red-500/30', 'text' => 'text-red-700 dark:text-red-400', 'dot' => 'bg-red-500'],
        default => ['label' => $status, 'desc' => '', 'bg' => 'bg-surface', 'border' => 'border-border', 'text' => 'text-content-secondary', 'dot' => 'bg-slate-400'],
    };

    $workflowSteps = match($status) {
        'draft' => 1,
        'menunggu' => 2,
        'disetujui' => 4,
        'ditolak' => 2,
        default => 1,
    };
@endphp

<div class="space-y-6">

    <div class="p-5 rounded-xl border {{ $statusConfig['bg'] }} {{ $statusConfig['border'] }}">
        <div class="flex items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-lg font-bold {{ $statusConfig['text'] }}">{{ $nomor }}</h3>
                    <x-status-badge :status="$status"/>
                </div>
                <p class="text-sm mt-1 {{ $statusConfig['text'] }} opacity-80">{{ $statusConfig['desc'] }}</p>
                <p class="text-xs mt-2 text-content-muted">Diajukan: {{ $created }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="px-5 py-4 border-b border-border-light">
                    <h4 class="text-sm font-semibold text-content">Informasi Permintaan</h4>
                </div>
                <div class="px-5 py-4">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-content-muted uppercase tracking-wide mb-1">OPD</p>
                                <p class="text-sm font-medium text-content">{{ $opd }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-content-muted uppercase tracking-wide mb-1">Tanggal</p>
                                <p class="text-sm text-content-secondary">{{ $tanggal }}</p>
                            </div>
                        </div>

                        <div class="border-t border-border-light pt-4">
                            <p class="text-xs font-medium text-content-muted uppercase tracking-wide mb-1">Sumber Dana</p>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-surface-alt text-content-secondary border border-border">
                                {{ $sumberDana }}
                            </span>
                        </div>

                        <div class="border-t border-border-light pt-4">
                            <p class="text-xs font-medium text-content-muted uppercase tracking-wide mb-1">Keperluan</p>
                            <p class="text-sm text-content-secondary leading-relaxed">{{ $keperluan }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="px-5 py-4 border-b border-border-light">
                    <h4 class="text-sm font-semibold text-content">Ringkasan Keuangan</h4>
                </div>
                <div class="px-5 py-4">
                    <div class="p-4 bg-surface rounded-lg border border-border mb-4">
                        <p class="text-xs font-medium text-content-muted uppercase tracking-wide mb-1">Nilai Permintaan</p>
                        <p class="text-2xl font-bold text-content tracking-tight">Rp {{ number_format($jumlah, 0, ',', '.') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 bg-surface rounded-lg">
                            <p class="text-xs font-medium text-content-muted mb-1">Status</p>
                            <x-status-badge :status="$status"/>
                        </div>
                        <div class="p-3 bg-surface rounded-lg">
                            <p class="text-xs font-medium text-content-muted mb-1">Sumber</p>
                            <span class="text-sm font-semibold text-content">{{ $sumberDana }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="px-5 py-4 border-b border-border-light">
                    <h4 class="text-sm font-semibold text-content">Alur Proses</h4>
                </div>
                <div class="px-5 py-5">
                    @if($status === 'ditolak')
                        <div class="space-y-0">
                            @foreach(['Permintaan Diajukan', 'Verifikasi BPKAD', 'Ditolak'] as $index => $step)
                                @php
                                    $stepNum = $index + 1;
                                    $isCompleted = $stepNum < 3;
                                    $isCurrent = $stepNum === 3;
                                @endphp
                                <div class="flex items-start gap-3">
                                    <div class="flex flex-col items-center">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                            @if($isCompleted) bg-primary text-white
                                            @elseif($isCurrent) bg-red-500 text-white ring-4 ring-red-100
                                            @else bg-surface-alt text-content-muted border-2 border-border
                                            @endif">
                                            @if($isCompleted)
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                                </svg>
                                            @elseif($isCurrent)
                                                <x-heroicon-o-x-mark class="w-3.5 h-3.5"/>
                                            @else
                                                {{ $stepNum }}
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 h-7 mt-1 {{ $isCompleted ? 'bg-primary' : 'bg-border' }}"></div>
                                        @endif
                                    </div>
                                    <div class="pb-5 pt-0.5">
                                        <p class="text-sm font-medium {{ $isCurrent ? 'text-red-600 dark:text-red-400' : ($isCompleted ? 'text-content-secondary' : 'text-content-muted') }}">
                                            {{ $step }}
                                        </p>
                                        @if($isCurrent)
                                            <p class="text-xs text-red-400 mt-0.5">Permintaan ditolak</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <x-timeline
                            :currentStep="$workflowSteps"
                            :steps="['Permintaan Diajukan', 'Verifikasi OPD', 'Verifikasi BPKAD', 'Persetujuan Kepala BPKAD', 'SPM Diterbitkan', 'Selesai']"
                        />
                    @endif
                </div>
            </div>

            <div class="bg-card rounded-xl border border-border shadow-sm">
                <div class="px-5 py-4 border-b border-border-light">
                    <h4 class="text-sm font-semibold text-content">Informasi Proses</h4>
                </div>
                <div class="px-5 py-4">
                    <div class="space-y-3">
                        <div class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-2 shrink-0"></div>
                            <p class="text-sm text-content-secondary">
                                @if($status === 'draft')
                                    Permintaan masih dalam bentuk draft. Klik "Ajukan" untuk mengirim ke proses persetujuan.
                                @elseif($status === 'menunggu')
                                    Permintaan sedang dalam proses verifikasi oleh OPD terkait dan BPKAD.
                                @elseif($status === 'disetujui')
                                    Permintaan telah disetujui. Proses selanjutnya adalah penerbitan SPM.
                                @else
                                    Permintaan ditolak. Silakan edit dan ajukan ulang jika diperlukan.
                                @endif
                            </p>
                        </div>
                        <div class="flex items-start gap-2.5">
                            <div class="w-1.5 h-1.5 rounded-full bg-slate-400 mt-2 shrink-0"></div>
                            <p class="text-sm text-content-secondary">Dana akan di-commit dari pagu setelah permintaan disetujui.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
