<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRekeningRequest;
use App\Http\Requests\UpdateRekeningRequest;
use App\Models\Rekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekeningKasController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $opdId = $user->isAdmin() ? null : $user->opd_id;

        $rekenings = Rekening::orderBy('kode')->get();

        $penerimaanSums = DB::table('transaksi_penerimaans as t')
            ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
            ->when($opdId, fn ($q) => $q->where('p.opd_id', $opdId))
            ->selectRaw('p.rekening_id, sum(t.realisasi) as total')
            ->groupBy('p.rekening_id')
            ->pluck('total', 'rekening_id');

        $pengeluaranSums = DB::table('pengeluarans')
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->selectRaw('rekening_id, sum(realisasi) as total')
            ->groupBy('rekening_id')
            ->pluck('total', 'rekening_id');

        $rekenings->each(function (Rekening $rekening) use ($penerimaanSums, $pengeluaranSums) {
            $total = (float) ($penerimaanSums[$rekening->id] ?? 0) - (float) ($pengeluaranSums[$rekening->id] ?? 0);
            $rekening->setAttribute('penerimaan_total', (float) ($penerimaanSums[$rekening->id] ?? 0));
            $rekening->setAttribute('pengeluaran_total', (float) ($pengeluaranSums[$rekening->id] ?? 0));
            $rekening->setAttribute('saldo_total', round($total, 2));
        });

        $kasOnly = $rekenings->where('tipe', 'kas');
        $totalKas = $kasOnly->sum('saldo_total');
        $totalPenerimaan = $rekenings->sum('penerimaan_total');
        $totalPengeluaran = $rekenings->sum('pengeluaran_total');

        return view('rekening-kas.index', compact(
            'rekenings', 'totalKas', 'totalPenerimaan', 'totalPengeluaran'
        ));
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
