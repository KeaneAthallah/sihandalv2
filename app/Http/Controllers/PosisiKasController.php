<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePosisiKasRequest;
use App\Http\Requests\UpdatePosisiKasRequest;
use App\Models\Opd;
use App\Models\PosisiKas;
use App\Models\Rekening;
use Illuminate\Http\Request;

class PosisiKasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $posisiKas = $this->applyOpdScope(PosisiKas::with(['opd', 'rekening']), $user)
            ->orderBy('tanggal', 'desc')
            ->get();

        $totalSaldoAwal = $posisiKas->sum('saldo_awal');
        $totalPenerimaan = $posisiKas->sum('penerimaan');
        $totalPengeluaran = $posisiKas->sum('pengeluaran');
        $totalSaldoAkhir = $posisiKas->sum('saldo_akhir');

        $rekenings = Rekening::where('tipe', 'kas')->orderBy('kode')->get();

        return view('posisi-kas.index', compact(
            'posisiKas', 'totalSaldoAwal', 'totalPenerimaan',
            'totalPengeluaran', 'totalSaldoAkhir', 'rekenings'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::where('tipe', 'kas')->orderBy('kode')->get();

        return view('posisi-kas.create', compact('opds', 'rekenings'));
    }

    public function edit(PosisiKas $posisiKas)
    {
        $this->authorizeOpdRecord($posisiKas, request()->user());
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::where('tipe', 'kas')->orderBy('kode')->get();

        return view('posisi-kas.edit', compact('posisiKas', 'opds', 'rekenings'));
    }

    public function store(StorePosisiKasRequest $request)
    {
        $data = $request->validated();
        $data['saldo_akhir'] = ($data['saldo_awal'] ?? 0) + ($data['penerimaan'] ?? 0) - ($data['pengeluaran'] ?? 0);

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        PosisiKas::create($data);

        return back()->with('success', 'Posisi kas berhasil ditambahkan.');
    }

    public function update(UpdatePosisiKasRequest $request, PosisiKas $posisiKas)
    {
        $this->authorizeOpdRecord($posisiKas, $request->user());
        $data = $request->validated();
        $data['saldo_akhir'] = ($data['saldo_awal'] ?? 0) + ($data['penerimaan'] ?? 0) - ($data['pengeluaran'] ?? 0);

        $posisiKas->update($data);

        return back()->with('success', 'Posisi kas berhasil diperbarui.');
    }

    public function destroy(PosisiKas $posisiKas)
    {
        $this->authorizeOpdRecord($posisiKas, request()->user());
        $posisiKas->delete();

        return back()->with('success', 'Posisi kas berhasil dihapus.');
    }
}
