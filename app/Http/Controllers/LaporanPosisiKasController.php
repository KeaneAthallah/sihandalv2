<?php

namespace App\Http\Controllers;

use App\Models\PosisiKas;
use Illuminate\Http\Request;

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
}
