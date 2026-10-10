@php
    $requests = [
        ['no' => 1, 'date' => '2026-07-10', 'number' => 'PD-2026-001', 'opd' => 'Dinas Pendidikan Daerah', 'source' => 'DAK', 'amount' => 2500000000, 'status' => 'pending', 'priority' => 'high'],
        ['no' => 2, 'date' => '2026-07-09', 'number' => 'PD-2026-002', 'opd' => 'Dinas Kesehatan Provinsi', 'source' => 'DAU', 'amount' => 1850000000, 'status' => 'approved', 'priority' => 'normal'],
        ['no' => 3, 'date' => '2026-07-08', 'number' => 'PD-2026-003', 'opd' => 'Dinas Sosial Provinsi', 'source' => 'DBH', 'amount' => 750000000, 'status' => 'pending', 'priority' => 'normal'],
        ['no' => 4, 'date' => '2026-07-07', 'number' => 'PD-2026-004', 'opd' => 'Dinas Bina Marga', 'source' => 'PAD', 'amount' => 3200000000, 'status' => 'realized', 'priority' => 'high'],
        ['no' => 5, 'date' => '2026-07-06', 'number' => 'PD-2026-005', 'opd' => 'RSUD Undata', 'source' => 'SILPA', 'amount' => 450000000, 'status' => 'rejected', 'priority' => 'low'],
        ['no' => 6, 'date' => '2026-07-05', 'number' => 'PD-2026-006', 'opd' => 'Dinas Cipta Karya', 'source' => 'DAK', 'amount' => 1200000000, 'status' => 'approved', 'priority' => 'normal'],
        ['no' => 7, 'date' => '2026-07-04', 'number' => 'PD-2026-007', 'opd' => 'BPBD Provinsi', 'source' => 'Hibah', 'amount' => 890000000, 'status' => 'pending', 'priority' => 'high'],
        ['no' => 8, 'date' => '2026-07-03', 'number' => 'PD-2026-008', 'opd' => 'Satpol PP', 'source' => 'DBH', 'amount' => 320000000, 'status' => 'realized', 'priority' => 'low'],
    ];

    $sourceColors = [
        'DAK' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/30/80',
        'DAU' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30/80',
        'DBH' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/30/80',
        'PAD' => 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-200/80',
        'SILPA' => 'bg-cyan-50 text-cyan-700 border-cyan-200/80',
        'Hibah' => 'bg-pink-50 text-pink-700 border-pink-200/80',
    ];
@endphp

<x-card :padding="false">
    <div class="px-5 py-4 border-b border-border-light">
        <h3 class="text-sm font-semibold text-content">Daftar Permintaan Dana</h3>
        <p class="text-xs text-content-muted mt-0.5">Data demo untuk referensi tampilan</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[800px]">
            <thead>
                <tr class="border-b border-border-light">
                    <th class="text-left px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide w-12">No</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide">Tanggal</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide">Nomor</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide">OPD</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide">Sumber Dana</th>
                    <th class="text-right px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide">Nilai</th>
                    <th class="text-center px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide">Status</th>
                    <th class="text-center px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide">Prioritas</th>
                    <th class="text-center px-5 py-3 text-xs font-medium text-content-muted uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light">
                @foreach($requests as $req)
                    <tr class="hover:bg-surface transition-colors">
                        <td class="px-5 py-3 text-xs text-content-muted font-medium">{{ $req['no'] }}</td>
                        <td class="px-5 py-3 text-sm text-content-secondary">{{ \Carbon\Carbon::parse($req['date'])->format('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <span class="text-sm font-mono font-semibold text-primary">{{ $req['number'] }}</span>
                        </td>
                        <td class="px-5 py-3 text-sm text-content-secondary max-w-[180px] truncate" title="{{ $req['opd'] }}">{{ $req['opd'] }}</td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $sourceColors[$req['source']] ?? 'bg-surface text-content-secondary border-border/80' }}">
                                {{ $req['source'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm font-semibold text-content text-right whitespace-nowrap">
                            Rp {{ number_format($req['amount'], 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <x-status-badge :status="$req['status']"/>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <x-priority-badge :priority="$req['priority']"/>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button class="p-1.5 text-content-muted hover:text-primary hover:bg-primary/10 rounded-lg transition" title="Lihat Detail">
                                    <x-heroicon-o-eye class="w-4 h-4"/>
                                </button>
                                <button class="p-1.5 text-content-muted hover:text-amber-600 hover:dark:text-amber-400 hover:bg-amber-50 hover:dark:bg-amber-500/10 rounded-lg transition" title="Edit">
                                    <x-heroicon-o-pencil class="w-4 h-4"/>
                                </button>
                                <button class="p-1.5 text-content-muted hover:text-content-secondary hover:bg-surface-alt rounded-lg transition" title="Lainnya">
                                    <x-heroicon-o-ellipsis-vertical class="w-4 h-4"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-border-light flex items-center justify-between">
        <p class="text-sm text-content-muted">Menampilkan <span class="font-medium text-content-secondary">1-8</span> dari <span class="font-medium text-content-secondary">24</span> permintaan</p>
        <div class="flex items-center gap-1">
            <button class="px-3 py-1.5 text-sm text-content-muted bg-surface rounded-lg cursor-not-allowed">Sebelumnya</button>
            <button class="px-3 py-1.5 text-sm text-white bg-primary rounded-lg font-medium shadow-sm">1</button>
            <button class="px-3 py-1.5 text-sm text-content-secondary bg-surface rounded-lg hover:bg-surface-alt transition">2</button>
            <button class="px-3 py-1.5 text-sm text-content-secondary bg-surface rounded-lg hover:bg-surface-alt transition">3</button>
            <button class="px-3 py-1.5 text-sm text-content-secondary bg-surface rounded-lg hover:bg-surface-alt transition">Selanjutnya</button>
        </div>
    </div>
</x-card>
