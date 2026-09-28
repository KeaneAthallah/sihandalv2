<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\PermintaanDanaResource;
use App\Http\Resources\PersetujuanResource;
use App\Models\PermintaanDana;
use App\Services\PermintaanDanaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PersetujuanController extends ApiController
{
    public function __construct(private readonly PermintaanDanaService $workflow) {}

    /**
     * Approval records of one permintaan (newest first).
     */
    public function index(Request $request, PermintaanDana $permintaanDana): JsonResponse
    {
        $this->authorizeOpd($request, $permintaanDana->opd_id);

        $permintaanDana->load(['persetujuans.user']);

        return $this->success(
            PersetujuanResource::collection($permintaanDana->persetujuans)->resolve(),
            'Data persetujuan berhasil diambil.',
        );
    }

    /**
     * menunggu -> disetujui. Admin-only (enforced by can:approve route
     * middleware). Realizes funds on the linked Belanja; expected business
     * failures return 422.
     */
    public function approve(Request $request, PermintaanDana $permintaanDana): JsonResponse
    {
        $this->authorizeOpd($request, $permintaanDana->opd_id);

        try {
            $permintaanDana = $this->workflow->approve($permintaanDana, $request->user());
        } catch (RuntimeException $e) {
            return $this->businessError('Unable to perform operation', ['business' => [$e->getMessage()]]);
        }

        return $this->success(new PermintaanDanaResource($permintaanDana->load(['opd', 'sumberDana'])), 'Permintaan dana berhasil disetujui.');
    }

    /**
     * menunggu -> ditolak. Releases committed funds; records the rejection.
     */
    public function reject(Request $request, PermintaanDana $permintaanDana): JsonResponse
    {
        $this->authorizeOpd($request, $permintaanDana->opd_id);

        $validated = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);
        $catatan = $validated['catatan'] ?? null;

        try {
            $permintaanDana = $this->workflow->reject($permintaanDana, $request->user(), is_string($catatan) ? $catatan : null);
        } catch (RuntimeException $e) {
            return $this->businessError('Unable to perform operation', ['business' => [$e->getMessage()]]);
        }

        return $this->success(new PermintaanDanaResource($permintaanDana->load(['opd', 'sumberDana'])), 'Permintaan dana ditolak.');
    }
}
