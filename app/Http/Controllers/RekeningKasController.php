<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRekeningRequest;
use App\Http\Requests\UpdateRekeningRequest;
use App\Models\Rekening;
use Illuminate\Http\Request;

class RekeningKasController extends Controller
{
    public function index(Request $request)
    {
        $rekenings = Rekening::orderBy('kode')->get();
        $totalSaldo = $rekenings->sum('saldo');
        $kasOnly = $rekenings->where('tipe', 'kas');
        $totalKas = $kasOnly->sum('saldo');

        return view('rekening-kas.index', compact('rekenings', 'totalSaldo', 'totalKas'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        return view('rekening-kas.create');
    }

    public function edit(Rekening $rekening)
    {
        $this->authorizeAdmin();

        return view('rekening-kas.edit', compact('rekening'));
    }

    public function store(StoreRekeningRequest $request)
    {
        $this->authorizeAdmin();
        Rekening::create($request->validated());

        return back()->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function update(UpdateRekeningRequest $request, Rekening $rekening)
    {
        $this->authorizeAdmin();
        $rekening->update($request->validated());

        return back()->with('success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(Rekening $rekening)
    {
        $this->authorizeAdmin();
        $rekening->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }

    protected function authorizeAdmin(): void
    {
        if (! request()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengelola rekening kas.');
        }
    }
}
