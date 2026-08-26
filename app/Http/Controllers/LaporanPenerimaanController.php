<?php

namespace App\Http\Controllers;

use App\Models\Penerimaan;
use Illuminate\Http\Request;

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
}
