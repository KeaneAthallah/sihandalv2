<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StorePenerimaanApiRequest;
use App\Http\Resources\PenerimaanResource;
use App\Models\Penerimaan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Penerimaan::query()
            ->with(['opd', 'rekening', 'subRekening', 'tahunAnggaran'])
            ->withCount(['transaksiPenerimaans']);

        if (! $user->isAdmin() || ! $request->filled('opd_id')) {
            $query->where(fn ($q) => $user->isAdmin()
                ? $q
                : $q->where('opd_id', $user->opd_id));
        }

        if ($request->filled('opd_id') && $user->isAdmin()) {
            $query->where('opd_id', $request->input('opd_id'));
        }

        $query->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')))
            ->when($request->filled('sub_rekening_id'), fn ($q) => $q->where('sub_rekening_id', $request->input('sub_rekening_id')))
            ->when($request->filled('tahun_anggaran_id'), fn ($q) => $q->where('tahun_anggaran_id', $request->input('tahun_anggaran_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereHas('transaksiPenerimaans', fn ($t) => $t->whereDate('tanggal', '>=', $request->input('tanggal_dari'))))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereHas('transaksiPenerimaans', fn ($t) => $t->whereDate('tanggal', '<=', $request->input('tanggal_sampai'))))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->whereHas('rekening', fn ($r) => $r->where('nama', 'like', $term)->orWhere('kode', 'like', $term)));
            });

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            $items = $query->orderBy('target', 'desc')->get();

            return $this->success(
                PenerimaanResource::collection($items)->resolve(),
                'Data penerimaan berhasil diambil.',
            );
        }

        $paginator = $query->orderBy('target', 'desc')->paginate($perPage);

        return $this->paginated($paginator, PenerimaanResource::class, 'Data penerimaan berhasil diambil.');
    }

    public function store(StorePenerimaanApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        $penerimaan = DB::transaction(function () use ($request, &$data): Penerimaan {
            if (! $request->user()->isAdmin()) {
                $data['opd_id'] = $request->user()->opd_id;
            }

            return Penerimaan::create($data);
        });

        return $this->success(new PenerimaanResource($penerimaan->fresh(['opd', 'rekening', 'subRekening', 'tahunAnggaran'])), 'Penerimaan berhasil ditambahkan.', 201);
    }

    public function show(Request $request, Penerimaan $penerimaan): JsonResponse
    {
        $this->authorizePenerimaan($request->user(), $penerimaan);

        $penerimaan->load(['opd', 'rekening', 'subRekening', 'tahunAnggaran']);

        return $this->success(new PenerimaanResource($penerimaan), 'Data penerimaan berhasil diambil.');
    }

    public function update(StorePenerimaanApiRequest $request, Penerimaan $penerimaan): JsonResponse
    {
        $this->authorizePenerimaan($request->user(), $penerimaan);

        $data = $request->validated();

        DB::transaction(function () use ($request, $penerimaan, &$data): void {
            if (! $request->user()->isAdmin()) {
                $data['opd_id'] = $request->user()->opd_id;
            }

            $penerimaan->update($data);
        });

        return $this->success(new PenerimaanResource($penerimaan->fresh(['opd', 'rekening', 'subRekening', 'tahunAnggaran'])), 'Penerimaan berhasil diperbarui.');
    }

    public function destroy(Request $request, Penerimaan $penerimaan): JsonResponse
    {
        $this->authorizePenerimaan($request->user(), $penerimaan);

        if ($penerimaan->transaksiPenerimaans()->exists()) {
            return $this->businessError('Penerimaan tidak dapat dihapus', [
                'penerimaan' => ['Penerimaan memiliki transaksi sehingga tidak dapat dihapus.'],
            ]);
        }

        $penerimaan->delete();

        return $this->success(message: 'Penerimaan berhasil dihapus.');
    }

    /**
     * Same OPD rule as the web controller: admins may touch all records,
     * province-wide masters (opd_id null) are admin-only.
     */
    private function authorizePenerimaan(User $user, Penerimaan $penerimaan): void
    {
        if ($user->isAdmin()) {
            return;
        }

        if ($penerimaan->opd_id === null || (int) $penerimaan->opd_id !== (int) $user->opd_id) {
            abort(403, 'Unauthorized');
        }
    }
}
