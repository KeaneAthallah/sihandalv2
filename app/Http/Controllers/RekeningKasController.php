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
        return view('rekening-kas.create');
    }

    public function edit(Rekening $rekening)
    {
        return view('rekening-kas.edit', compact('rekening'));
    }

    public function store(StoreRekeningRequest $request)
    {
        Rekening::create($request->validated());

        return back()->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function update(UpdateRekeningRequest $request, Rekening $rekening)
    {
        $rekening->update($request->validated());

        return back()->with('success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(Rekening $rekening)
    {
        $rekening->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }
}
