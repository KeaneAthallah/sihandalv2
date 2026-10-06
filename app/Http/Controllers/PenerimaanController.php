<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenerimaanRequest;
use App\Http\Requests\UpdatePenerimaanRequest;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\TahunAnggaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Penerimaan::with([
            'opd', 'rekening', 'subRekening', 'tahunAnggaran',
            'transaksiPenerimaans' => fn ($t) => $t
                ->when(
                    $request->filled('tanggal_dari'),
                    fn ($q) => $t->whereDate('tanggal', '>=', $request->input('tanggal_dari'))
                )
                ->when(
                    $request->filled('tanggal_sampai'),
                    fn ($q) => $t->whereDate('tanggal', '<=', $request->input('tanggal_sampai'))
                ),
        ]);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query = $this->applyOpdScope($query, $user);
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query
            ->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')))
            ->when($request->filled('sub_rekening_id'), fn ($q) => $q->where('sub_rekening_id', $request->input('sub_rekening_id')));

        // Tanggal filters apply to the realization transactions, not the master.
        // The eager load above is constrained by the same window so the
        // in-memory realisasi/persentase sums only count transactions inside the period.
        $query->when(
            $request->filled('tanggal_dari'),
            fn ($q) => $q->whereHas('transaksiPenerimaans', fn ($t) => $t->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
        )->when(
            $request->filled('tanggal_sampai'),
            fn ($q) => $q->whereHas('transaksiPenerimaans', fn ($t) => $t->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
        );

        // Computed totals come from the transaction relationship (accessors),
        // so the in-memory collection sum works on imported masters too.
        $allPenerimaans = (clone $query)->orderBy('target', 'desc')->get();
        $totalTarget = $allPenerimaans->sum('target');
        $totalRealisasi = $allPenerimaans->sum('realisasi');
        $persentase = $totalTarget > 0 ? round(($totalRealisasi / $totalTarget) * 100, 1) : 0;

        $penerimaans = $query->orderBy('target', 'desc')->paginate(15);
        unset($allPenerimaans);

        $opds = $this->userOpds($user);
        $rekenings = Rekening::orderBy('kode')->get();
        $filters = $request->only(['opd_id', 'rekening_id', 'sub_rekening_id', 'tanggal_dari', 'tanggal_sampai']);

        return view('penerimaan.index', compact(
            'penerimaans', 'totalTarget', 'totalRealisasi', 'persentase',
            'opds', 'rekenings', 'filters'
        ));
    }

    public function create()
    {
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::where('tipe', 'pendapatan')->orderBy('kode')->get();
        $subRekeningsByParent = $this->subRekeningsByParent();
        $tahunAnggarans = TahunAnggaran::orderByDesc('tahun')->get();

        return view('penerimaan.create', compact('opds', 'rekenings', 'subRekeningsByParent', 'tahunAnggarans'));
    }

    public function edit(Penerimaan $penerimaan)
    {
        $this->authorizeOpdRecord($penerimaan, request()->user());
        $penerimaan->load(['subRekening']);
        $opds = $this->userOpds(request()->user());
        $rekenings = Rekening::orderBy('kode')->get();
        $subRekeningsByParent = $this->subRekeningsByParent();
        $tahunAnggarans = TahunAnggaran::orderByDesc('tahun')->get();

        return view('penerimaan.edit', compact('penerimaan', 'opds', 'rekenings', 'subRekeningsByParent', 'tahunAnggarans'));
    }

    public function store(StorePenerimaanRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, &$data) {
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

        $data = $request->validated();

        DB::transaction(function () use ($request, $penerimaan, &$data) {
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

    /**
     * Sub rekenings (details) grouped by their induk, for the
     * rekening utama -> sub rekening cascade on the form.
     *
     * @return array<string, array<int, array{id: string, label: string}>>
     */
    private function subRekeningsByParent(): array
    {
        return Rekening::where('tipe', 'pendapatan')
            ->whereNotNull('parent_id')
            ->orderBy('kode')
            ->get(['id', 'parent_id', 'kode', 'nama'])
            ->groupBy('parent_id')
            ->map(fn ($rows) => $rows->map(fn ($r) => [
                'id' => (string) $r->id,
                'label' => $r->kode.' - '.$r->nama,
            ])->values())
            ->all();
    }
}
