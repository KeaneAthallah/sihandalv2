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
@endphp

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-100">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">No</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nomor Permintaan</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">OPD</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sumber Dana</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Nilai Permintaan</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Prioritas</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($requests as $req)
                    <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer">
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $req['no'] }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ \Carbon\Carbon::parse($req['date'])->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-primary">{{ $req['number'] }}</td>
                        <td class="px-6 py-4 text-sm text-slate-700 max-w-[200px] truncate">{{ $req['opd'] }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold
                                @if($req['source'] === 'DAK') bg-blue-50 text-blue-700
                                @elseif($req['source'] === 'DAU') bg-emerald-50 text-emerald-700
                                @elseif($req['source'] === 'DBH') bg-amber-50 text-amber-700
                                @elseif($req['source'] === 'PAD') bg-purple-50 text-purple-700
                                @elseif($req['source'] === 'SILPA') bg-cyan-50 text-cyan-700
                                @else bg-slate-50 text-slate-700
                                @endif">
                                {{ $req['source'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-800 text-right">
                            Rp {{ number_format($req['amount'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-status-badge :status="$req['status']"/>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <x-priority-badge :priority="$req['priority']"/>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button class="p-1.5 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all" title="Lihat Detail">
                                    <x-heroicon-o-eye class="w-4 h-4"/>
                                </button>
                                <button class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-all" title="Lainnya">
                                    <x-heroicon-o-ellipsis-vertical class="w-4 h-4"/>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
        <p class="text-sm text-slate-500">Menampilkan 1-8 dari 24 permintaan</p>
        <div class="flex items-center gap-1">
            <button class="px-3 py-1.5 text-sm text-slate-400 bg-slate-50 rounded-lg cursor-not-allowed">Sebelumnya</button>
            <button class="px-3 py-1.5 text-sm text-white bg-primary rounded-lg font-medium">1</button>
            <button class="px-3 py-1.5 text-sm text-slate-600 bg-slate-50 rounded-lg hover:bg-slate-100 transition-all">2</button>
            <button class="px-3 py-1.5 text-sm text-slate-600 bg-slate-50 rounded-lg hover:bg-slate-100 transition-all">3</button>
            <button class="px-3 py-1.5 text-sm text-slate-600 bg-slate-50 rounded-lg hover:bg-slate-100 transition-all">Selanjutnya</button>
        </div>
    </div>
</div>
