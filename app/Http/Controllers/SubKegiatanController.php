<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubKegiatanRequest;
use App\Http\Requests\UpdateSubKegiatanRequest;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubKegiatanController extends Controller
{
    public function index(Request $request, Kegiatan $kegiatan)
    {
        $user = $request->user();
        $this->authorizeOpdRecord($kegiatan, $user);

        $subKegiatans = $kegiatan->subKegiatans()
            ->with(['belanjas.rekening', 'belanjas.sumberDana'])
            ->orderBy('kode_sub_kegiatan')
            ->get();

        return view('sub-kegiatan.index', compact('kegiatan', 'subKegiatans'));
    }

    public function create(Request $request, Kegiatan $kegiatan)
    {
        $this->authorizeOpdRecord($kegiatan, $request->user());

        return view('sub-kegiatan.create', compact('kegiatan'));
    }

    public function store(StoreSubKegiatanRequest $request, Kegiatan $kegiatan)
    {
        $this->authorizeOpdRecord($kegiatan, $request->user());

        DB::transaction(function () use ($request, $kegiatan) {
            $data = $request->validated();
            $realisasi = (float) ($data['realisasi'] ?? 0);
            $data['realisasi'] = $realisasi;
            $data['persentase'] = $data['pagu'] > 0 ? round($realisasi / $data['pagu'] * 100, 2) : 0;
            $data['kegiatan_id'] = $kegiatan->id;

            SubKegiatan::create($data);
        });

        return back()->with('success', 'Sub kegiatan berhasil ditambahkan.');
    }

    public function edit(Request $request, Kegiatan $kegiatan, SubKegiatan $subKegiatan)
    {
        $this->authorizeOpdRecord($kegiatan, $request->user());

        return view('sub-kegiatan.edit', compact('kegiatan', 'subKegiatan'));
    }

    public function update(UpdateSubKegiatanRequest $request, Kegiatan $kegiatan, SubKegiatan $subKegiatan)
    {
        $this->authorizeOpdRecord($kegiatan, $request->user());

        DB::transaction(function () use ($request, $subKegiatan) {
            $data = $request->validated();
            $realisasi = (float) ($data['realisasi'] ?? 0);
            $data['realisasi'] = $realisasi;
            $data['persentase'] = $data['pagu'] > 0 ? round($realisasi / $data['pagu'] * 100, 2) : 0;

            $subKegiatan->update($data);
        });

        return back()->with('success', 'Sub kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Kegiatan $kegiatan, SubKegiatan $subKegiatan)
    {
        $this->authorizeOpdRecord($kegiatan, $request->user());
        $subKegiatan->delete();

        return back()->with('success', 'Sub kegiatan berhasil dihapus.');
    }
}
