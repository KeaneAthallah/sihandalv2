<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\TransaksiPenerimaanBkuResource;
use App\Models\RekeningBank;
use App\Models\TransaksiPenerimaan;
use App\Models\TransaksiPenerimaanBku;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Nested BKU endpoints on a TransaksiPenerimaan:
 *   POST   /transaksi-penerimaan/{transaksi}/bkus
 *   PATCH  /transaksi-penerimaan/{transaksi}/bkus/{bku}
 *   DELETE /transaksi-penerimaan/{transaksi}/bkus/{bku}
 *
 * The SUM(BKU) == realisasi rule is enforced on every write: a single BKU row
 * may only be added or edited if the resulting total stays consistent.
 */
class TransaksiPenerimaanBkuController extends ApiController
{
    public function store(Request $request, TransaksiPenerimaan $transaksiPenerimaan): JsonResponse
    {
        $this->authorizeTransaction($transaksiPenerimaan, $request->user());

        $validated = $this->validateBku($request);

        $existingTotal = (float) $transaksiPenerimaan->bkus()->sum('nilai');
        $newTotal = $existingTotal + (float) $validated['nilai'];

        if (abs($newTotal - (float) $transaksiPenerimaan->realisasi) > 0.0001) {
            return $this->businessError('Total nilai BKU harus sama dengan nilai transaksi penerimaan.', [
                'bkus' => ["Total nilai BKU setelah penambahan ({$newTotal}) harus sama dengan realisasi transaksi ({$transaksiPenerimaan->realisasi})."],
            ]);
        }

        $bku = DB::transaction(function () use ($transaksiPenerimaan, $validated): TransaksiPenerimaanBku {
            return $transaksiPenerimaan->bkus()->create($validated);
        });

        return $this->success(new TransaksiPenerimaanBkuResource($bku->load('rekeningBank')), 'BKU berhasil ditambahkan.', 201);
    }

    public function update(Request $request, TransaksiPenerimaan $transaksiPenerimaan, TransaksiPenerimaanBku $bku): JsonResponse
    {
        $this->authorizeTransaction($transaksiPenerimaan, $request->user());
        $this->authorizeBkuBelongsToTransaction($transaksiPenerimaan, $bku);

        $validated = $this->validateBku($request);

        $otherTotal = (float) $transaksiPenerimaan->bkus()->where('id', '!=', $bku->id)->sum('nilai');
        $newTotal = $otherTotal + (float) $validated['nilai'];

        if (abs($newTotal - (float) $transaksiPenerimaan->realisasi) > 0.0001) {
            return $this->businessError('Total nilai BKU harus sama dengan nilai transaksi penerimaan.', [
                'bkus' => ["Total nilai BKU setelah perubahan ({$newTotal}) harus sama dengan realisasi transaksi ({$transaksiPenerimaan->realisasi})."],
            ]);
        }

        DB::transaction(fn () => $bku->update($validated));

        return $this->success(new TransaksiPenerimaanBkuResource($bku->fresh('rekeningBank')), 'BKU berhasil diperbarui.');
    }

    public function destroy(Request $request, TransaksiPenerimaan $transaksiPenerimaan, TransaksiPenerimaanBku $bku): JsonResponse
    {
        $this->authorizeTransaction($transaksiPenerimaan, $request->user());
        $this->authorizeBkuBelongsToTransaction($transaksiPenerimaan, $bku);

        DB::transaction(fn () => $bku->delete());

        return $this->success(message: 'BKU berhasil dihapus.');
    }

    /**
     * Validates one BKU row including active bank status.
     *
     * @return array<string, mixed>
     */
    private function validateBku(Request $request): array
    {
        $validated = Validator::make($request->all(), [
            'nomor_bku' => ['required', 'string', 'max:100'],
            'tanggal_bku' => ['required', 'date'],
            'nilai' => ['required', 'numeric', 'min:0'],
            'rekening_bank_id' => ['nullable', 'integer', 'exists:rekening_banks,id'],
        ])->validate();

        if (($validated['rekening_bank_id'] ?? null) !== null) {
            $bank = RekeningBank::find($validated['rekening_bank_id']);

            if ($bank !== null && ! $bank->is_active) {
                throw ValidationException::withMessages([
                    'rekening_bank_id' => ['Rekening bank tidak aktif dan tidak dapat digunakan.'],
                ]);
            }
        }

        return $validated;
    }

    private function authorizeTransaction(TransaksiPenerimaan $transaksi, User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $masterOpd = $transaksi->penerimaan?->opd_id;
        abort_unless($masterOpd !== null && (int) $masterOpd === (int) $user->opd_id, 403, 'Unauthorized');
    }

    private function authorizeBkuBelongsToTransaction(TransaksiPenerimaan $transaksi, TransaksiPenerimaanBku $bku): void
    {
        abort_unless((int) $bku->transaksi_penerimaan_id === (int) $transaksi->id, 404, 'Resource not found');
    }
}
