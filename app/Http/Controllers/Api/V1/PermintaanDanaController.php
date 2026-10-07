<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StorePermintaanDanaApiRequest;
use App\Http\Resources\PermintaanDanaResource;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Services\PermintaanDanaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PermintaanDanaController extends ApiController
{
    public function __construct(private readonly PermintaanDanaService $workflow) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PermintaanDana::query()
            ->with(['opd', 'kegiatan', 'subKegiatan', 'belanja', 'sumberDana'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->when($request->filled('opd_id') && $user->isAdmin(), fn ($q) => $q->where('opd_id', $request->input('opd_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
            ->when($request->filled('search'), fn ($q) => $q->where('nomor_permintaan', 'like', '%'.$request->string('search').'%'))
            ->orderBy('created_at', 'desc');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(PermintaanDanaResource::collection($query->get())->resolve(), 'Data permintaan dana berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), PermintaanDanaResource::class, 'Data permintaan dana berhasil diambil.');
    }

    public function store(StorePermintaanDanaApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $data['sumber_dana'] = SumberDana::findOrFail($data['sumber_dana_id'])->nama_sumber_dana;
        $data['nomor_permintaan'] = $this->workflow->nextNomorPermintaan();
        $data['status'] = 'draft';
        $data['tahun_anggaran_id'] = TahunAnggaran::currentActive()?->id;

        $permintaan = PermintaanDana::create($data);

        return $this->success(new PermintaanDanaResource($permintaan->load(['opd', 'sumberDana'])), 'Permintaan dana berhasil dibuat sebagai draft.', 201);
    }

    public function show(Request $request, PermintaanDana $permintaanDana): JsonResponse
    {
        $this->authorizeOpd($request, $permintaanDana->opd_id);

        $permintaanDana->load(['opd', 'kegiatan', 'subKegiatan', 'belanja', 'sumberDana', 'persetujuans.user']);

        return $this->success(new PermintaanDanaResource($permintaanDana), 'Data permintaan dana berhasil diambil.');
    }

    public function update(StorePermintaanDanaApiRequest $request, PermintaanDana $permintaanDana): JsonResponse
    {
        $this->authorizeOpd($request, $permintaanDana->opd_id);

        if (! in_array($permintaanDana->status, ['draft', 'ditolak'], true)) {
            return $this->businessError('Permintaan dana tidak dapat diubah', [
                'status' => ['Hanya permintaan draft atau ditolak yang dapat diedit.'],
            ]);
        }

        $data = $request->validated();
        $data['sumber_dana'] = SumberDana::findOrFail($data['sumber_dana_id'])->nama_sumber_dana;

        // Status is never client-settable here.
        unset($data['status']);

        $permintaanDana->update($data);

        return $this->success(new PermintaanDanaResource($permintaanDana->fresh(['opd', 'sumberDana'])), 'Permintaan dana berhasil diperbarui.');
    }

    /**
     * Explicit workflow action: draft -> menunggu. Commits funds on the linked
     * Belanja inside a transaction with row locking; expected business failures
     * return 422, never 500.
     */
    public function submit(Request $request, PermintaanDana $permintaanDana): JsonResponse
    {
        $this->authorizeOpd($request, $permintaanDana->opd_id);

        try {
            $permintaanDana = $this->workflow->submit($permintaanDana, $request->user());
        } catch (RuntimeException $e) {
            return $this->businessError('Unable to perform operation', ['business' => [$e->getMessage()]]);
        }

        return $this->success(new PermintaanDanaResource($permintaanDana->load(['opd', 'sumberDana'])), 'Permintaan dana berhasil diajukan dan menunggu persetujuan.');
    }
}
