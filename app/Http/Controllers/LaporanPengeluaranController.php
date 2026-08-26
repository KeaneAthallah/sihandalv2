<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;

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
}
