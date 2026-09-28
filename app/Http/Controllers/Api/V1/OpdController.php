<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\OpdResource;
use App\Models\Opd;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpdController extends ApiController
{
    /**
     * OPD list scoped to the authenticated user: admins see all, OPD users
     * only their own OPD.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Opd::query()
            ->withCount(['kegiatans', 'penerimaans', 'pengeluarans', 'upts', 'programs'])
            ->withSum('kegiatans as total_pagu_kegiatan', 'pagu')
            ->orderBy('nama');

        if (! $user->isAdmin()) {
            $query->where('id', $user->opd_id);
        }

        $query->when($request->filled('search'), function ($q) use ($request): void {
            $term = '%'.$request->string('search').'%';
            $q->where(fn ($w) => $w->where('nama', 'like', $term)->orWhere('kode', 'like', $term));
        });

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(OpdResource::collection($query->get())->resolve(), 'Data OPD berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), OpdResource::class, 'Data OPD berhasil diambil.');
    }

    public function show(Request $request, Opd $opd): JsonResponse
    {
        $this->authorizeOpd($request, $opd->id);

        $opd->load(['programs', 'upts', 'dinas', 'unit']);

        return $this->success(
            new OpdResource($opd),
            'Data OPD berhasil diambil.',
        );
    }
}
