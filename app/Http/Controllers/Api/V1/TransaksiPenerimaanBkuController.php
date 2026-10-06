<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\TransaksiPenerimaanBkuResource;
use App\Models\Rekening;
use App\Models\RekeningBank;
use App\Models\TransaksiPenerimaan;
use App\Models\TransaksiPenerimaanBku;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
            'opd_id' => ['nullable', 'integer', 'exists:opds,id'],
            'nomor_bku' => ['required', 'string', 'max:100'],
            'tanggal_bku' => ['required', 'date'],
            'nilai' => ['required', 'numeric', 'min:0'],
            'rekening_id' => ['nullable', 'integer', Rule::exists('rekenings', 'id')->where(fn ($q) => $q->where('tipe', 'pendapatan'))],
            'sub_rekening_id' => ['nullable', 'integer', 'exists:rekenings,id'],
            'rekening_bank_id' => ['nullable', 'integer', 'exists:rekening_banks,id'],
        ])->validate();

        $this->validateBkuRekening($validated);

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

    /**
     * A BKU's rekening utama must be tipe pendapatan and a sub
     * rekening must be a detail of it.
     *
     * @param  array<string, mixed>  $validated
     */
    private function validateBkuRekening(array $validated): void
    {
        $rekeningId = $validated['rekening_id'] ?? null;
        $subRekeningId = $validated['sub_rekening_id'] ?? null;

        if ($rekeningId !== null) {
            $rekening = Rekening::find($rekeningId);

            if ($rekening !== null && $rekening->tipe !== 'pendapatan') {
                throw ValidationException::withMessages([
                    'rekening_id' => ['Rekening utama pada BKU harus bertipe pendapatan.'],
                ]);
            }
        }

        if ($subRekeningId === null) {
            return;
        }

        $sub = Rekening::find($subRekeningId);

        if ($sub === null) {
            return;
        }

        if ($rekeningId === null) {
            throw ValidationException::withMessages([
                'sub_rekening_id' => ['Pilih rekening utama terlebih dahulu sebelum memilih sub rekening.'],
            ]);
        }

        if ((int) $sub->parent_id !== (int) $rekeningId) {
            throw ValidationException::withMessages([
                'sub_rekening_id' => ['Sub rekening harus merupakan detail dari rekening utama pada BKU ini.'],
            ]);
        }
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
