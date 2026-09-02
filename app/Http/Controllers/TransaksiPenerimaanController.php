<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiPenerimaanRequest;
use App\Http\Requests\UpdateTransaksiPenerimaanRequest;
use App\Models\Penerimaan;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use Illuminate\Http\Request;

class TransaksiPenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = TransaksiPenerimaan::with(['penerimaan.opd', 'penerimaan.rekening', 'penerimaan.sumberDana'])
            ->whereHas('penerimaan', function ($p) use ($user) {
                if (! $user->isAdmin()) {
                    $p->where('opd_id', $user->opd_id);
                }
            });

        $query
            ->when($request->filled('penerimaan_id'), fn ($q) => $q->where('penerimaan_id', $request->input('penerimaan_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')));

        $transaksis = $query->orderByDesc('tanggal')->get();

        $totalRealisasi = $transaksis->sum('realisasi');
        $penerimaans = $this->authorizedMasters($user);
        $filters = $request->only(['penerimaan_id', 'tanggal_dari', 'tanggal_sampai']);

        return view('transaksi-penerimaan.index', compact('transaksis', 'totalRealisasi', 'penerimaans', 'filters'));
    }

    public function create()
    {
        $penerimaans = $this->authorizedMasters(request()->user());

        return view('transaksi-penerimaan.create', compact('penerimaans'));
    }

    public function edit(TransaksiPenerimaan $transaksiPenerimaan)
    {
        $this->authorizeTransaction($transaksiPenerimaan, request()->user());
        $penerimaans = $this->authorizedMasters(request()->user());

        return view('transaksi-penerimaan.edit', compact('transaksiPenerimaan', 'penerimaans'));
    }

    public function store(StoreTransaksiPenerimaanRequest $request)
    {
        TransaksiPenerimaan::create($request->validated());

        return back()->with('success', 'Transaksi Penerimaan berhasil ditambahkan.');
    }

    public function update(UpdateTransaksiPenerimaanRequest $request, TransaksiPenerimaan $transaksiPenerimaan)
    {
        $this->authorizeTransaction($transaksiPenerimaan, $request->user());
        $transaksiPenerimaan->update($request->validated());

        return back()->with('success', 'Transaksi Penerimaan berhasil diperbarui.');
    }

    public function destroy(TransaksiPenerimaan $transaksiPenerimaan)
    {
        $this->authorizeTransaction($transaksiPenerimaan, request()->user());
        $transaksiPenerimaan->delete();

        return back()->with('success', 'Transaksi Penerimaan berhasil dihapus.');
    }

    private function authorizedMasters($user)
    {
        $query = Penerimaan::with(['opd', 'sumberDana']);

        if (! $user->isAdmin()) {
            $query->where('opd_id', $user->opd_id);
        }

        return $query->orderBy('nama_sumber_dana')->get();
    }

    private function authorizeTransaction(TransaksiPenerimaan $transaksi, ?User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $masterOpd = $transaksi->penerimaan?->opd_id;
        abort_unless($masterOpd !== null && (int) $masterOpd === (int) $user->opd_id, 403);
    }
}
