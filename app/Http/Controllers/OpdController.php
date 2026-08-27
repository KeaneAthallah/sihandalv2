<?php

namespace App\Http\Controllers;

use App\Models\Opd;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $opds = $this->applyOpdScope(Opd::query(), $user, 'id')
            ->withCount(['kegiatans', 'penerimaans', 'pengeluarans'])
            ->withSum('kegiatans as total_pagu_kegiatan', 'pagu')
            ->orderBy('nama')
            ->get();

        $totalOpd = $opds->count();
        $totalPagu = $opds->sum('total_pagu_kegiatan');

        return view('opd.index', compact('opds', 'totalOpd', 'totalPagu'));
    }

    public function show(Request $request, Opd $opd)
    {
        $this->authorizeOpdRecord($opd, $request->user(), 'id');
        $opd->load(['kegiatans.program', 'kegiatans.sumberDana']);

        $totalPagu = $opd->kegiatans->sum('pagu');
        $totalRealisasi = $opd->kegiatans->sum('realisasi');

        return view('opd.show', compact('opd', 'totalPagu', 'totalRealisasi'));
    }
}
