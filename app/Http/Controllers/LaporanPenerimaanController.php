<?php

namespace App\Http\Controllers;

use App\Models\Penerimaan;
use App\Models\SumberDana;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Penerimaan::with(['opd', 'sumberDana', 'rekening']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query = $this->applyOpdScope($query, $user);
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));

        $penerimaans = $query->orderBy('tanggal', 'desc')->get();

        $totalTarget = $penerimaans->sum('target');
        $totalRealisasi = $penerimaans->sum('realisasi');
        $persentase = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 1) : 0;

        $opds = $this->userOpds($user);
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $filters = $request->only(['opd_id', 'sumber_dana_id', 'tanggal_dari', 'tanggal_sampai']);

        return view('laporan-penerimaan.index', compact(
            'penerimaans', 'totalTarget', 'totalRealisasi', 'persentase',
            'opds', 'sumberDanas', 'filters'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        $query = Penerimaan::with(['opd', 'sumberDana', 'rekening']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query = $this->applyOpdScope($query, $user);
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));

        $penerimaans = $query->orderBy('tanggal', 'desc')->get();

        $filename = 'laporan-penerimaan-'.now()->format('Y-m-d').'.csv';

        return response()->stream(function () use ($penerimaans) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Tanggal', 'OPD', 'Sumber Dana', 'Target', 'Realisasi', 'Persentase (%)', 'Selisih',
            ]);

            foreach ($penerimaans as $idx => $item) {
                fputcsv($handle, [
                    $idx + 1,
                    $item->tanggal?->format('d/m/Y') ?? '-',
                    $item->opd->nama ?? '-',
                    $item->sumberDana?->nama_sumber_dana ?? $item->nama_sumber_dana ?? '-',
                    $item->target,
                    $item->realisasi,
                    $item->persentase,
                    $item->realisasi - $item->target,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
