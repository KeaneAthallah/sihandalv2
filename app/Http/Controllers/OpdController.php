<?php

namespace App\Http\Controllers;

use App\Models\Dinas;
use App\Models\Opd;
use App\Models\Program;
use App\Models\SumberDana;
use App\Models\Unit;
use App\Models\Upt;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $opds = $this->applyOpdScope(Opd::query(), $user, 'id')
            ->withCount(['kegiatans', 'penerimaans', 'pengeluarans', 'upts', 'programs'])
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
        $opd->load(['programs', 'upts', 'dinas', 'unit']);

        $user = $request->user();

        // Programs are global (programs.opd_id is null), so scope the hierarchy by
        // the OPD of the kegiatan rows rather than the program row itself.
        $query = Program::with([
            'kegiatans' => fn ($kegiatanQuery) => $kegiatanQuery
                ->where('opd_id', $opd->id)
                ->with(['subKegiatans.belanjas.rekening', 'subKegiatans.belanjas.sumberDana'])
                ->when($request->filled('tahun_anggaran_id'), fn ($q) => $q->where('tahun_anggaran_id', $request->get('tahun_anggaran_id')))
                ->when($request->filled('sumber_dana_id'), fn ($q) => $q->whereHas('subKegiatans.belanjas', fn ($bq) => $bq->where('sumber_dana_id', $request->get('sumber_dana_id')))),
        ])
            ->whereHas('kegiatans', fn ($q) => $q->where('opd_id', $opd->id))
            ->when($request->filled('nmskpd'), fn ($q) => $q->whereHas('kegiatans', fn ($oq) => $oq->where('opd_id', $opd->id)->whereHas('opd', fn ($oq2) => $oq2->where('nmskpd', $request->get('nmskpd')))))
            ->when($request->filled('dinas_id'), fn ($q) => $q->whereHas('kegiatans', fn ($oq) => $oq->where('opd_id', $opd->id)->whereHas('opd', fn ($oq2) => $oq2->where('dinas_id', $request->get('dinas_id')))))
            ->when($request->filled('unit_id'), fn ($q) => $q->whereHas('kegiatans', fn ($oq) => $oq->where('opd_id', $opd->id)->whereHas('opd', fn ($oq2) => $oq2->where('unit_id', $request->get('unit_id')))))
            ->when($request->filled('program_id'), fn ($q) => $q->where('id', $request->get('program_id')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->whereHas('kegiatans.subKegiatans.belanjas', fn ($bq) => $bq->where('sumber_dana_id', $request->get('sumber_dana_id'))))
            ->when($request->filled('tahun_anggaran_id'), fn ($q) => $q->whereHas('kegiatans', fn ($kq) => $kq->where('tahun_anggaran_id', $request->get('tahun_anggaran_id'))))
            ->orderBy('kode_program')
            ->get();

        $programs = $query;
        $totalPaguProgram = $programs->sum(fn ($p) => $p->kegiatans->sum(fn ($k) => $k->subKegiatans->sum(fn ($s) => $s->belanjas->sum('pagu'))));
        $totalRealisasi = $programs->sum(fn ($p) => $p->kegiatans->sum(fn ($k) => $k->subKegiatans->sum(fn ($s) => $s->belanjas->sum('realisasi'))));

        $totalPagu = $opd->belanjas()->sum('pagu');
        if ($totalPagu <= 0) {
            $totalPagu = $totalPaguProgram;
        }

        $dinas = Dinas::where('opd_id', $opd->id)->orderBy('nama')->get();
        $units = Unit::where('opd_id', $opd->id)->orderBy('nama')->get();
        $upts = Upt::where('opd_id', $opd->id)->orderBy('nama')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        $filters = $request->only(['nmskpd', 'dinas_id', 'unit_id', 'program_id', 'sumber_dana_id', 'tahun_anggaran_id']);

        return view('opd.show', compact(
            'opd', 'programs', 'totalPagu', 'totalRealisasi',
            'dinas', 'units', 'upts', 'sumberDanas', 'filters'
        ));
    }
}
