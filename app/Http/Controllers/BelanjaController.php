<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBelanjaRequest;
use App\Http\Requests\UpdateBelanjaRequest;
use App\Models\Belanja;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BelanjaController extends Controller
{
    public function index(Request $request, SubKegiatan $subKegiatan)
    {
        $kegiatan = $subKegiatan->kegiatan;
        $this->authorizeOpdRecord($kegiatan, $request->user());

        $belanjaQuery = $subKegiatan->belanjas()->with(['rekening', 'sumberDana']);

        $belanjas = $belanjaQuery->paginate(15);
        $totalPagu = (clone $belanjaQuery)->sum('pagu');
        $totalRealisasi = (clone $belanjaQuery)->sum('realisasi');
        $totalCommit = (clone $belanjaQuery)->sum('dana_di_commit');

        $opds = $this->userOpds($request->user());
        $rekenings = Rekening::orderBy('kode')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        return view('belanja.index', compact(
            'subKegiatan', 'kegiatan', 'belanjas', 'totalPagu',
            'totalRealisasi', 'totalCommit', 'opds', 'rekenings', 'sumberDanas'
        ));
    }

    public function create(Request $request, SubKegiatan $subKegiatan)
    {
        $kegiatan = $subKegiatan->kegiatan;
        $this->authorizeOpdRecord($kegiatan, $request->user());

        $opds = $this->userOpds($request->user());
        $rekenings = Rekening::orderBy('kode')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        return view('belanja.create', compact('subKegiatan', 'kegiatan', 'opds', 'rekenings', 'sumberDanas'));
    }

    public function store(StoreBelanjaRequest $request, SubKegiatan $subKegiatan)
    {
        $kegiatan = $subKegiatan->kegiatan;
        $this->authorizeOpdRecord($kegiatan, $request->user());

        DB::transaction(function () use ($request, $subKegiatan) {
            $data = $request->validated();
            $data['sub_kegiatan_id'] = $subKegiatan->id;
            $data['dana_di_commit'] = 0;

            if (! $request->user()->isAdmin()) {
                $data['opd_id'] = $request->user()->opd_id;
            }

            Belanja::create($data);
        });

        return back()->with('success', 'Belanja berhasil ditambahkan.');
    }

    public function edit(Request $request, SubKegiatan $subKegiatan, Belanja $belanja)
    {
        $kegiatan = $subKegiatan->kegiatan;
        $this->authorizeOpdRecord($kegiatan, $request->user());

        $opds = $this->userOpds($request->user());
        $rekenings = Rekening::orderBy('kode')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();

        return view('belanja.edit', compact('subKegiatan', 'kegiatan', 'belanja', 'opds', 'rekenings', 'sumberDanas'));
    }

    public function update(UpdateBelanjaRequest $request, SubKegiatan $subKegiatan, Belanja $belanja)
    {
        $kegiatan = $subKegiatan->kegiatan;
        $this->authorizeOpdRecord($kegiatan, $request->user());

        DB::transaction(function () use ($request, $belanja) {
            $data = $request->validated();
            $belanja->update($data);
        });

        return back()->with('success', 'Belanja berhasil diperbarui.');
    }

    public function destroy(Request $request, SubKegiatan $subKegiatan, Belanja $belanja)
    {
        $kegiatan = $subKegiatan->kegiatan;
        $this->authorizeOpdRecord($kegiatan, $request->user());
        $belanja->delete();

        return back()->with('success', 'Belanja berhasil dihapus.');
    }
}
