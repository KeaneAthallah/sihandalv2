<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerimaanRequest;
use App\Http\Requests\UpdatePenerimaanRequest;
use App\Models\Penerimaan;
use App\Models\Rekening;
use Illuminate\Http\Request;

class PenerimaanController extends Controller
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
        $rekenings = Rekening::orderBy('kode')->get();

        return view('penerimaan.index', compact(
            'penerimaans', 'totalTarget', 'totalRealisasi', 'persentase', 'opds', 'rekenings'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::orderBy('kode')->get();

        return view('penerimaan.create', compact('opds', 'rekenings'));
    }

    public function edit(Penerimaan $penerimaan)
    {
        $this->authorizeOpdRecord($penerimaan, request()->user());
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::orderBy('kode')->get();

        return view('penerimaan.edit', compact('penerimaan', 'opds', 'rekenings'));
    }

    public function store(StorePenerimaanRequest $request)
    {
        $data = $request->validated();
        $data['persentase'] = $data['target'] > 0 ? round(($data['realisasi'] ?? 0) / $data['target'] * 100, 2) : 0;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        Penerimaan::create($data);

        return back()->with('success', 'Penerimaan berhasil ditambahkan.');
    }

    public function update(UpdatePenerimaanRequest $request, Penerimaan $penerimaan)
    {
        $this->authorizeOpdRecord($penerimaan, $request->user());
        $data = $request->validated();
        $data['persentase'] = $data['target'] > 0 ? round(($data['realisasi'] ?? 0) / $data['target'] * 100, 2) : 0;

        $penerimaan->update($data);

        return back()->with('success', 'Penerimaan berhasil diperbarui.');
    }

    public function destroy(Penerimaan $penerimaan)
    {
        $this->authorizeOpdRecord($penerimaan, request()->user());
        $penerimaan->delete();

        return back()->with('success', 'Penerimaan berhasil dihapus.');
    }
}
