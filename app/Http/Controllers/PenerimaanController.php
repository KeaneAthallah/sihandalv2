<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerimaanRequest;
use App\Http\Requests\UpdatePenerimaanRequest;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Penerimaan::with(['opd', 'sumberDana', 'rekening', 'tahunAnggaran', 'transaksiPenerimaans']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query = $this->applyOpdScope($query, $user);
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')));

        // Tanggal filters apply to the realization transactions, not the master.
        $query->when(
            $request->filled('tanggal_dari'),
            fn ($q) => $q->whereHas('transaksiPenerimaans', fn ($t) => $t->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
        )->when(
            $request->filled('tanggal_sampai'),
            fn ($q) => $q->whereHas('transaksiPenerimaans', fn ($t) => $t->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
        );

        $penerimaans = $query->orderBy('target', 'desc')->get();

        // Computed totals come from the transaction relationship (accessors),
        // so the in-memory collection sum works on imported masters too.
        $totalTarget = $penerimaans->sum('target');
        $totalRealisasi = $penerimaans->sum('realisasi');
        $persentase = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 1) : 0;

        $opds = $this->userOpds($user);
        $rekenings = Rekening::orderBy('kode')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $filters = $request->only(['opd_id', 'sumber_dana_id', 'rekening_id', 'tanggal_dari', 'tanggal_sampai']);

        return view('penerimaan.index', compact(
            'penerimaans', 'totalTarget', 'totalRealisasi', 'persentase',
            'opds', 'rekenings', 'sumberDanas', 'filters'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::orderBy('kode')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $tahunAnggarans = TahunAnggaran::orderByDesc('tahun')->get();

        return view('penerimaan.create', compact('opds', 'rekenings', 'sumberDanas', 'tahunAnggarans'));
    }

    public function edit(Penerimaan $penerimaan)
    {
        $this->authorizeOpdRecord($penerimaan, request()->user());
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::orderBy('kode')->get();
        $sumberDanas = SumberDana::orderBy('nama_sumber_dana')->get();
        $tahunAnggarans = TahunAnggaran::orderByDesc('tahun')->get();

        return view('penerimaan.edit', compact('penerimaan', 'opds', 'rekenings', 'sumberDanas', 'tahunAnggarans'));
    }

    public function store(StorePenerimaanRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, &$data) {
            if ($data['sumber_dana_id'] ?? null) {
                $sumberDana = SumberDana::find($data['sumber_dana_id']);
                $data['nama_sumber_dana'] = $sumberDana?->nama_sumber_dana;
            }

            if (! $request->user()->isAdmin()) {
                $data['opd_id'] = $request->user()->opd_id;
            }

            Penerimaan::create($data);
        });

        return back()->with('success', 'Penerimaan berhasil ditambahkan.');
    }

    public function update(UpdatePenerimaanRequest $request, Penerimaan $penerimaan)
    {
        $this->authorizeOpdRecord($penerimaan, $request->user());

        DB::transaction(function () use ($request, $penerimaan) {
            $data = $request->validated();

            if ($data['sumber_dana_id'] ?? null) {
                $sumberDana = SumberDana::find($data['sumber_dana_id']);
                $data['nama_sumber_dana'] = $sumberDana?->nama_sumber_dana;
            }

            if (! $request->user()->isAdmin()) {
                $data['opd_id'] = $request->user()->opd_id;
            }

            $penerimaan->update($data);
        });

        return back()->with('success', 'Penerimaan berhasil diperbarui.');
    }

    public function destroy(Penerimaan $penerimaan)
    {
        $this->authorizeOpdRecord($penerimaan, request()->user());

        if ($penerimaan->transaksiPenerimaans()->exists()) {
            return back()->withErrors(['penerimaan' => 'Penerimaan memiliki transaksi sehingga tidak dapat dihapus.']);
        }

        $penerimaan->delete();

        return back()->with('success', 'Penerimaan berhasil dihapus.');
    }
}
