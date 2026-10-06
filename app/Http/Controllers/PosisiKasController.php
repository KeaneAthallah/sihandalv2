<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePosisiKasRequest;
use App\Http\Requests\UpdatePosisiKasRequest;
use App\Models\PosisiKas;
use Illuminate\Http\Request;

class PosisiKasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $posisiQuery = $this->applyOpdScope(PosisiKas::with('opd'), $user)
            ->orderBy('tanggal', 'desc');

        $totalSaldo = (clone $posisiQuery)->sum('saldo');
        $totalCount = (clone $posisiQuery)->count();

        $posisiKas = $posisiQuery->paginate(15);

        return view('posisi-kas.index', compact(
            'posisiKas', 'totalSaldo', 'totalCount'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());

        return view('posisi-kas.create', compact('opds'));
    }

    public function edit(PosisiKas $posisiKas)
    {
        $this->authorizeOpdRecord($posisiKas, request()->user());
        $opds = $this->userOpds(request()->user());

        return view('posisi-kas.edit', compact('posisiKas', 'opds'));
    }

    public function store(StorePosisiKasRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        PosisiKas::create($data);

        return back()->with('success', 'Posisi kas berhasil ditambahkan.');
    }

    public function update(UpdatePosisiKasRequest $request, PosisiKas $posisiKas)
    {
        $this->authorizeOpdRecord($posisiKas, $request->user());

        $posisiKas->update($request->validated());

        return back()->with('success', 'Posisi kas berhasil diperbarui.');
    }

    public function destroy(PosisiKas $posisiKas)
    {
        $this->authorizeOpdRecord($posisiKas, request()->user());
        $posisiKas->delete();

        return back()->with('success', 'Posisi kas berhasil dihapus.');
    }
}
