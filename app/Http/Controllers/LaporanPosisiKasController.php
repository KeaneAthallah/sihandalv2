<?php

namespace App\Http\Controllers;

use App\Models\PosisiKas;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanPosisiKasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $posisiKas = $this->applyOpdScope(PosisiKas::with(['opd', 'rekening']), $user)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalSaldoAwal = $posisiKas->sum('saldo_awal');
        $totalPenerimaan = $posisiKas->sum('penerimaan');
        $totalPengeluaran = $posisiKas->sum('pengeluaran');
        $totalSaldoAkhir = $posisiKas->sum('saldo_akhir');

        $opds = $this->userOpds($user);

        return view('laporan-posisi-kas.index', compact(
            'posisiKas', 'totalSaldoAwal', 'totalPenerimaan',
            'totalPengeluaran', 'totalSaldoAkhir', 'opds'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $posisiKas = $this->applyOpdScope(PosisiKas::with(['opd', 'rekening']), $user)
            ->orderBy('tanggal', 'desc')
            ->get();

        $filename = 'laporan-posisi-kas-'.now()->format('Y-m-d').'.csv';

        return response()->stream(function () use ($posisiKas) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Tanggal', 'OPD', 'Rekening', 'Saldo Awal', 'Penerimaan', 'Pengeluaran', 'Saldo Akhir',
            ]);

            foreach ($posisiKas as $idx => $item) {
                fputcsv($handle, [
                    $idx + 1,
                    $item->tanggal?->format('d/m/Y') ?? '-',
                    $item->opd->nama ?? '-',
                    $item->rekening->nama ?? '-',
                    $item->saldo_awal,
                    $item->penerimaan,
                    $item->pengeluaran,
                    $item->saldo_akhir,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
