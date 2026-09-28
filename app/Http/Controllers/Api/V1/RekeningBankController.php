<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreRekeningBankApiRequest;
use App\Http\Requests\Api\UpdateRekeningBankApiRequest;
use App\Http\Resources\RekeningBankResource;
use App\Models\RekeningBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * RekeningBank is a GLOBAL master: no OPD scoping applies (intentional).
 */
class RekeningBankController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = RekeningBank::query()
            ->when($request->boolean('active'), fn ($q) => $q->where('is_active', true))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w
                    ->where('bank_name', 'like', $term)
                    ->orWhere('account_number', 'like', $term)
                    ->orWhere('account_name', 'like', $term));
            })
            ->orderBy('bank_name')
            ->orderBy('account_number');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(RekeningBankResource::collection($query->get())->resolve(), 'Data rekening bank berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), RekeningBankResource::class, 'Data rekening bank berhasil diambil.');
    }

    /**
     * Convenience list of active banks for booking forms.
     */
    public function active(): JsonResponse
    {
        $banks = RekeningBank::query()
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get();

        return $this->success(RekeningBankResource::collection($banks)->resolve(), 'Data rekening bank aktif berhasil diambil.');
    }

    public function show(RekeningBank $rekeningBank): JsonResponse
    {
        return $this->success(new RekeningBankResource($rekeningBank), 'Data rekening bank berhasil diambil.');
    }

    public function store(StoreRekeningBankApiRequest $request): JsonResponse
    {
        $bank = RekeningBank::create($request->validated());

        return $this->success(new RekeningBankResource($bank), 'Rekening bank berhasil ditambahkan.', 201);
    }

    public function update(UpdateRekeningBankApiRequest $request, RekeningBank $rekeningBank): JsonResponse
    {
        $rekeningBank->update($request->validated());

        return $this->success(new RekeningBankResource($rekeningBank->fresh()), 'Rekening bank berhasil diperbarui.');
    }

    public function destroy(Request $request, RekeningBank $rekeningBank): JsonResponse
    {
        // A bank referenced by BKU rows must not be hard-deleted; deactivate it
        // instead so history remains intact.
        if ($rekeningBank->is_active) {
            $used = DB::table('transaksi_penerimaan_bkus')
                ->where('rekening_bank_id', $rekeningBank->id)
                ->exists();

            if ($used) {
                return $this->businessError('Rekening bank tidak dapat dihapus', [
                    'rekening_bank' => ['Rekening bank sudah digunakan pada transaksi. Nonaktifkan rekening ini sebagai gantinya.'],
                ]);
            }
        }

        $rekeningBank->delete();

        return $this->success(message: 'Rekening bank berhasil dihapus.');
    }
}
