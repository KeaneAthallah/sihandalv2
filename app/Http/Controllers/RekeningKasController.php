<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ImportsMasterData;
use App\Http\Requests\ImportMasterDataRequest;
use App\Http\Requests\StoreRekeningRequest;
use App\Http\Requests\UpdateRekeningRequest;
use App\Imports\Definitions\RekeningImportDefinition;
use App\Models\Rekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekeningKasController extends Controller
{
    use ImportsMasterData;

    public function index(Request $request)
    {
        $user = $request->user();
        $opdId = $user->isAdmin() ? null : $user->opd_id;

        $rekenings = Rekening::with('parent')
            ->withCount('children')
            ->orderBy('kode')
            ->orderBy('parent_id')
            ->paginate(15);

        $penerimaanSums = DB::table('transaksi_penerimaans as t')
            ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
            ->when($opdId, fn ($q) => $q->where('p.opd_id', $opdId))
            ->selectRaw('p.rekening_id, sum(t.realisasi) as total')
            ->groupBy('p.rekening_id')
            ->pluck('total', 'rekening_id');

        $pengeluaranSums = DB::table('pengeluarans')
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->selectRaw('rekening_id, sum(jumlah) as total')
            ->groupBy('rekening_id')
            ->pluck('total', 'rekening_id');

        $rekenings->getCollection()->each(function (Rekening $rekening) use ($penerimaanSums, $pengeluaranSums) {
            $rekening->setAttribute('penerimaan_total', (float) ($penerimaanSums[$rekening->id] ?? 0));
            $rekening->setAttribute('pengeluaran_total', (float) ($pengeluaranSums[$rekening->id] ?? 0));
            $rekening->setAttribute('saldo_total', round($rekening->penerimaan_total - $rekening->pengeluaran_total, 2));
        });

        $totalPenerimaan = $penerimaanSums->sum();
        $totalPengeluaran = $pengeluaranSums->sum();
        $totalKas = Rekening::where('tipe', 'kas')->get()->reduce(function (float $carry, Rekening $rekening) use ($penerimaanSums, $pengeluaranSums) {
            return $carry + ((float) ($penerimaanSums[$rekening->id] ?? 0) - (float) ($pengeluaranSums[$rekening->id] ?? 0));
        }, 0.0);

        return view('rekening-kas.index', compact(
            'rekenings', 'totalKas', 'totalPenerimaan', 'totalPengeluaran'
        ));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $rekenings = Rekening::orderBy('kode')->get();

        return view('rekening-kas.create', compact('rekenings'));
    }

    public function edit(Rekening $rekening)
    {
        $this->authorizeAdmin();

        // A rekening cannot be its own parent, nor the parent of its ancestor
        // (that would loop). Exclude itself and every descendant from options.
        $excludedIds = collect([$rekening->id])->merge($this->descendantIds($rekening))->all();
        $rekenings = Rekening::whereNotIn('id', $excludedIds)
            ->orderBy('kode')
            ->get();

        return view('rekening-kas.edit', compact('rekening', 'rekenings'));
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

        if ($rekening->children()->exists()) {
            return back()->withErrors(['rekening' => 'Rekening memiliki rekening detail sehingga tidak dapat dihapus.']);
        }

        $rekening->delete();

        return back()->with('success', 'Rekening berhasil dihapus.');
    }

    private function descendantIds(Rekening $rekening): array
    {
        $ids = [];
        $queue = [$rekening->id];

        while ($queue !== []) {
            $id = array_shift($queue);
            $children = Rekening::where('parent_id', $id)->pluck('id')->all();
            $ids = array_merge($ids, $children);
            $queue = array_merge($queue, $children);
        }

        return $ids;
    }

    public function template()
    {
        return $this->templateMaster(new RekeningImportDefinition);
    }

    public function import(ImportMasterDataRequest $request)
    {
        return $this->importMaster($request, new RekeningImportDefinition);
    }

    protected function authorizeAdmin(): void
    {
        if (! request()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengelola rekening kas.');
        }
    }
}
