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
            ->withCount(['programs', 'penerimaans', 'pengeluarans'])
            ->withSum('sumberDanas as total_pagu_sumber_dana', 'pagu')
            ->orderBy('nama')
            ->get();

        $totalOpd = $opds->count();
        $totalPagu = $opds->sum('total_pagu');

        return view('opd.index', compact('opds', 'totalOpd', 'totalPagu'));
    }

    public function show(Request $request, Opd $opd)
    {
        $this->authorizeOpdRecord($opd, $request->user(), 'id');
        $opd->load(['sumberDanas', 'programs']);

        $totalPagu = $opd->sumberDanas->sum('pagu');
        $totalRealisasi = $opd->sumberDanas->sum('realisasi');

        return view('opd.show', compact('opd', 'totalPagu', 'totalRealisasi'));
    }
}
