<?php

namespace App\Http\Controllers;

use App\Models\Penerimaan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $penerimaans = $this->applyOpdScope(Penerimaan::with('opd'), $user)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalTarget = $penerimaans->sum('target');
        $totalRealisasi = $penerimaans->sum('realisasi');
        $persentase = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 1) : 0;

        $opds = $this->userOpds($user);

        return view('laporan-penerimaan.index', compact(
            'penerimaans', 'totalTarget', 'totalRealisasi', 'persentase', 'opds'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $penerimaans = $this->applyOpdScope(Penerimaan::with('opd'), $user)
            ->orderBy('tanggal', 'desc')
            ->get();

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
                    $item->nama_sumber_dana ?? '-',
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
