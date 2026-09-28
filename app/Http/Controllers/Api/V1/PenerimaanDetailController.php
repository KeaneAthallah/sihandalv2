<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PenerimaanDetailResource;
use App\Models\PenerimaanDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PenerimaanDetailController extends ApiController
{
    /**
     * Detail rows are managed through the parent Penerimaan save (same as web).
     * This read endpoint exists for clients that need the flat detail list.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PenerimaanDetail::query()
            ->with(['sumberDana', 'penerimaan.opd'])
            ->when($request->filled('penerimaan_id'), fn ($q) => $q->where('penerimaan_id', $request->input('penerimaan_id')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->whereHas('penerimaan', fn ($p) => $user->isAdmin() ? $p : $p->where('opd_id', $user->opd_id));

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(PenerimaanDetailResource::collection($query->get())->resolve(), 'Data penerimaan detail berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), PenerimaanDetailResource::class, 'Data penerimaan detail berhasil diambil.');
    }

    public function show(Request $request, PenerimaanDetail $penerimaanDetail): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user->isAdmin() || (int) $penerimaanDetail->penerimaan?->opd_id === (int) $user->opd_id,
            403,
            'Unauthorized',
        );

        $penerimaanDetail->load(['sumberDana', 'penerimaan']);

        return $this->success(new PenerimaanDetailResource($penerimaanDetail), 'Data penerimaan detail berhasil diambil.');
    }
}
