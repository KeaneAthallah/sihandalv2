<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransaksiPenerimaanRequest;
use App\Http\Requests\UpdateTransaksiPenerimaanRequest;
use App\Models\Penerimaan;
use App\Models\Rekening;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiPenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = TransaksiPenerimaan::with([
            'penerimaan.opd',
            'penerimaan.rekening',
            'penerimaan.sumberDana',
            'bkus.rekening',
        ])
            ->whereHas('penerimaan', function ($p) use ($user) {
                if (! $user->isAdmin()) {
                    $p->where('opd_id', $user->opd_id);
                }
            });

        $query
            ->when($request->filled('penerimaan_id'), fn ($q) => $q->where('penerimaan_id', $request->input('penerimaan_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
            ->orderByDesc('tanggal');

        $totalRealisasi = (clone $query)->sum('realisasi');
        $transaksis = $query->paginate(15);

        $penerimaans = $this->authorizedMasters($user);
        $filters = $request->only(['penerimaan_id', 'tanggal_dari', 'tanggal_sampai']);

        return view('transaksi-penerimaan.index', compact('transaksis', 'totalRealisasi', 'penerimaans', 'filters'));
    }

    public function create()
    {
        $penerimaans = $this->authorizedMasters(request()->user());
        $rekenings = $this->pendapatanRekenings();

        return view('transaksi-penerimaan.create', compact('penerimaans', 'rekenings'));
    }

    public function edit(TransaksiPenerimaan $transaksiPenerimaan)
    {
        $this->authorizeTransaction($transaksiPenerimaan, request()->user());
        $transaksiPenerimaan->load(['bkus.rekening', 'penerimaan.opd']);
        $penerimaans = $this->authorizedMasters(request()->user());
        $rekenings = $this->pendapatanRekenings();

        return view('transaksi-penerimaan.edit', compact('transaksiPenerimaan', 'penerimaans', 'rekenings'));
    }

    public function store(StoreTransaksiPenerimaanRequest $request, DocumentNumberService $numbers)
    {
        $data = $request->validated();
        $bkus = $data['bkus'] ?? [];
        unset($data['bkus']);

        $year = Carbon::parse($data['tanggal'])->year;
        $data['nomor_registrasi'] = $numbers->next('transaksi_penerimaan', 'REG', $year);

        $transaksi = DB::transaction(function () use ($data, $bkus) {
            $transaksi = TransaksiPenerimaan::create($data);

            foreach ($bkus as $bku) {
                unset($bku['id']);
                $transaksi->bkus()->create($bku);
            }

            return $transaksi;
        });

        return back()->with('success', "Transaksi Penerimaan ({$data['nomor_registrasi']}) beserta detail BKU berhasil ditambahkan.");
    }

    public function update(UpdateTransaksiPenerimaanRequest $request, TransaksiPenerimaan $transaksiPenerimaan)
    {
        $this->authorizeTransaction($transaksiPenerimaan, $request->user());

        // Guard against tampering: submitted BKU ids must belong to this
        // transaction, otherwise an attacker could touch another OPD's rows.
        $this->authorizeSubmittedBkus($transaksiPenerimaan, $request);

        $data = $request->validated();
        $bkus = $data['bkus'] ?? [];
        unset($data['bkus']);

        $submittedIds = collect($bkus)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        DB::transaction(function () use ($transaksiPenerimaan, $data, $bkus, $submittedIds) {
            $transaksiPenerimaan->update($data);

            // Non-submitted existing BKUs are removed.
            $transaksiPenerimaan->bkus()
                ->whereNotIn('id', $submittedIds)
                ->delete();

            foreach ($bkus as $bku) {
                $id = $bku['id'] ?? null;

                if ($id !== null) {
                    $existing = $transaksiPenerimaan->bkus()->find($id);
                    if ($existing) {
                        $existing->update($bku);

                        continue;
                    }
                }

                unset($bku['id']);
                $transaksiPenerimaan->bkus()->create($bku);
            }
        });

        return back()->with('success', 'Transaksi Penerimaan beserta detail BKU berhasil diperbarui.');
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

    private function pendapatanRekenings()
    {
        return Rekening::where('tipe', 'pendapatan')->orderBy('kode')->get();
    }

    private function authorizeTransaction(TransaksiPenerimaan $transaksi, ?User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $masterOpd = $transaksi->penerimaan?->opd_id;
        abort_unless($masterOpd !== null && (int) $masterOpd === (int) $user->opd_id, 403);
    }

    private function authorizeSubmittedBkus(TransaksiPenerimaan $transaksi, Request $request): void
    {
        $bkus = $request->input('bkus', []);

        $ids = collect($bkus)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        if ($ids === []) {
            return;
        }

        $ownedIds = $transaksi->bkus()->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();

        abort_unless(count($ownedIds) === count($ids), 403);
    }
}
