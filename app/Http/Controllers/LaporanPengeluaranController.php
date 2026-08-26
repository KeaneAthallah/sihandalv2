<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $pengeluarans = $this->applyOpdScope(Pengeluaran::with('opd'), $user)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalAnggaran = $pengeluarans->sum('anggaran');
        $totalRealisasi = $pengeluarans->sum('realisasi');
        $persentase = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100, 1) : 0;

        $opds = $this->userOpds($user);

        return view('laporan-pengeluaran.index', compact(
            'pengeluarans', 'totalAnggaran', 'totalRealisasi', 'persentase', 'opds'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $pengeluarans = $this->applyOpdScope(Pengeluaran::with('opd'), $user)
            ->orderBy('tanggal', 'desc')
            ->get();

        $filename = 'laporan-pengeluaran-'.now()->format('Y-m-d').'.csv';

        return response()->stream(function () use ($pengeluarans) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Tanggal', 'OPD', 'Kegiatan', 'Anggaran', 'Realisasi', 'Persentase (%)',
            ]);

            foreach ($pengeluarans as $idx => $item) {
                fputcsv($handle, [
                    $idx + 1,
                    $item->tanggal?->format('d/m/Y') ?? '-',
                    $item->opd->nama ?? '-',
                    $item->nama_kegiatan ?? '-',
                    $item->anggaran,
                    $item->realisasi,
                    $item->persentase,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
