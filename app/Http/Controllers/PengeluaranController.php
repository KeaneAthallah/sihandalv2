<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePengeluaranRequest;
use App\Http\Requests\UpdatePengeluaranRequest;
use App\Models\Pengeluaran;
use App\Models\Rekening;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
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

        return view('pengeluaran.index', compact(
            'pengeluarans', 'totalAnggaran', 'totalRealisasi', 'persentase', 'opds'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::orderBy('kode')->get();

        return view('pengeluaran.create', compact('opds', 'rekenings'));
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        $this->authorizeOpdRecord($pengeluaran, request()->user());
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::orderBy('kode')->get();

        return view('pengeluaran.edit', compact('pengeluaran', 'opds', 'rekenings'));
    }

    public function store(StorePengeluaranRequest $request)
    {
        $data = $request->validated();
        $data['persentase'] = $data['anggaran'] > 0 ? round(($data['realisasi'] ?? 0) / $data['anggaran'] * 100, 2) : 0;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        Pengeluaran::create($data);

        return back()->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function update(UpdatePengeluaranRequest $request, Pengeluaran $pengeluaran)
    {
        $this->authorizeOpdRecord($pengeluaran, $request->user());
        $data = $request->validated();
        $data['persentase'] = $data['anggaran'] > 0 ? round(($data['realisasi'] ?? 0) / $data['anggaran'] * 100, 2) : 0;

        $pengeluaran->update($data);

        return back()->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $this->authorizeOpdRecord($pengeluaran, request()->user());
        $pengeluaran->delete();

        return back()->with('success', 'Pengeluaran berhasil dihapus.');
    }
}
