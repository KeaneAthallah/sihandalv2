<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreTransaksiPenerimaanApiRequest;
use App\Http\Requests\Api\UpdateTransaksiPenerimaanApiRequest;
use App\Http\Resources\TransaksiPenerimaanResource;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use App\Services\DocumentNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiPenerimaanController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = TransaksiPenerimaan::query()
            ->with(['penerimaan.opd', 'penerimaan.rekening', 'penerimaan.sumberDana', 'bkus.rekeningBank'])
            ->whereHas('penerimaan', function ($p) use ($user): void {
                if (! $user->isAdmin()) {
                    $p->where('opd_id', $user->opd_id);
                }
            })
            ->when($request->filled('penerimaan_id'), fn ($q) => $q->where('penerimaan_id', $request->input('penerimaan_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
            ->when($request->filled('search'), fn ($q) => $q->where('nomor_registrasi', 'like', '%'.$request->string('search').'%'))
            ->orderByDesc('tanggal');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(TransaksiPenerimaanResource::collection($query->get())->resolve(), 'Data transaksi penerimaan berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), TransaksiPenerimaanResource::class, 'Data transaksi penerimaan berhasil diambil.');
    }

    public function store(StoreTransaksiPenerimaanApiRequest $request, DocumentNumberService $numbers): JsonResponse
    {
        $data = $request->validated();
        $bkus = $data['bkus'] ?? [];
        unset($data['bkus']);

        // nomor_registrasi is generated server-side (REG-XXXXX/YYYY), race-safe.
        $year = Carbon::parse($data['tanggal'])->year;
        $data['nomor_registrasi'] = $numbers->next('transaksi_penerimaan', 'REG', $year);

        $transaksi = DB::transaction(function () use ($data, $bkus): TransaksiPenerimaan {
            $transaksi = TransaksiPenerimaan::create($data);

            foreach ($bkus as $bku) {
                unset($bku['id']);
                $transaksi->bkus()->create($bku);
            }

            return $transaksi;
        });

        return $this->success(
            new TransaksiPenerimaanResource($transaksi->fresh(['penerimaan.opd', 'bkus.rekeningBank'])),
            'Transaksi Penerimaan berhasil ditambahkan.',
            201,
        );
    }

    public function show(Request $request, TransaksiPenerimaan $transaksiPenerimaan): JsonResponse
    {
        $this->authorizeTransaction($transaksiPenerimaan, $request->user());

        $transaksiPenerimaan->load(['penerimaan.opd', 'bkus.rekeningBank']);

        return $this->success(new TransaksiPenerimaanResource($transaksiPenerimaan), 'Data transaksi penerimaan berhasil diambil.');
    }

    public function update(UpdateTransaksiPenerimaanApiRequest $request, TransaksiPenerimaan $transaksiPenerimaan): JsonResponse
    {
        $this->authorizeTransaction($transaksiPenerimaan, $request->user());
        $this->authorizeSubmittedBkus($transaksiPenerimaan, $request);

        $data = $request->validated();
        $bkus = $data['bkus'] ?? [];
        unset($data['bkus']);

        // nomor_registrasi is immutable: never taken from request input.
        unset($data['nomor_registrasi']);

        $submittedIds = collect($bkus)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($transaksiPenerimaan, $data, $bkus, $submittedIds): void {
            $transaksiPenerimaan->update($data);

            $transaksiPenerimaan->bkus()->whereNotIn('id', $submittedIds)->delete();

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

        return $this->success(
            new TransaksiPenerimaanResource($transaksiPenerimaan->fresh(['penerimaan.opd', 'bkus.rekeningBank'])),
            'Transaksi Penerimaan berhasil diperbarui.',
        );
    }

    public function destroy(Request $request, TransaksiPenerimaan $transaksiPenerimaan): JsonResponse
    {
        $this->authorizeTransaction($transaksiPenerimaan, $request->user());

        $transaksiPenerimaan->delete();

        return $this->success(message: 'Transaksi Penerimaan berhasil dihapus.');
    }

    private function authorizeTransaction(TransaksiPenerimaan $transaksi, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $masterOpd = $transaksi->penerimaan?->opd_id;
        abort_unless($masterOpd !== null && (int) $masterOpd === (int) $user->opd_id, 403, 'Unauthorized');
    }

    /**
     * Submitted BKU ids must belong to this transaction (anti-tampering),
     * mirroring the web controller guard.
     */
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

        abort_unless(count($ownedIds) === count($ids), 403, 'Unauthorized');
    }
}
