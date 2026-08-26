<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSumberDanaRequest;
use App\Http\Requests\UpdateSumberDanaRequest;
use App\Models\SumberDana;
use Illuminate\Http\Request;

class SumberDanaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $opds = $this->userOpds($user);
        $sumberDanaTypes = $this->applyOpdScope(SumberDana::query(), $user)
            ->select('nama_sumber_dana')
            ->distinct()
            ->pluck('nama_sumber_dana');

        $totalPagu = $this->applyOpdScope(SumberDana::query(), $user)->sum('pagu');
        $totalRealisasi = $this->applyOpdScope(SumberDana::query(), $user)->sum('realisasi');
        $persentase = $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 1) : 0;

        $sumberDanaData = $this->applyOpdScope(SumberDana::query(), $user)
            ->select('nama_sumber_dana')
            ->selectRaw('SUM(pagu) as total_pagu')
            ->selectRaw('SUM(realisasi) as total_realisasi')
            ->groupBy('nama_sumber_dana')
            ->get();

        $sumberDanaRecords = $this->applyOpdScope(SumberDana::with('opd')->latest(), $user)->paginate(20);

        return view('sumber-dana.index', compact(
            'opds', 'sumberDanaTypes', 'totalPagu', 'totalRealisasi',
            'persentase', 'sumberDanaData', 'sumberDanaRecords'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());

        return view('sumber-dana.create', compact('opds'));
    }

    public function edit(SumberDana $sumberDana)
    {
        $this->authorizeOpdRecord($sumberDana, request()->user());
        $opds = $this->userOpds(request()->user());

        return view('sumber-dana.edit', compact('sumberDana', 'opds'));
    }

    public function store(StoreSumberDanaRequest $request)
    {
        $data = $request->validated();
        $data['persentase'] = $data['pagu'] > 0 ? round(($data['realisasi'] ?? 0) / $data['pagu'] * 100, 2) : 0;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        SumberDana::create($data);

        return back()->with('success', 'Sumber dana berhasil ditambahkan.');
    }

    public function update(UpdateSumberDanaRequest $request, SumberDana $sumberDana)
    {
        $this->authorizeOpdRecord($sumberDana, $request->user());
        $data = $request->validated();
        $data['persentase'] = $data['pagu'] > 0 ? round(($data['realisasi'] ?? 0) / $data['pagu'] * 100, 2) : 0;

        $sumberDana->update($data);

        return back()->with('success', 'Sumber dana berhasil diperbarui.');
    }

    public function destroy(SumberDana $sumberDana)
    {
        $this->authorizeOpdRecord($sumberDana, request()->user());
        $sumberDana->delete();

        return back()->with('success', 'Sumber dana berhasil dihapus.');
    }
}
