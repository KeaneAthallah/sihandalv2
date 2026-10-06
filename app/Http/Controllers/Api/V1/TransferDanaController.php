<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreTransferDanaApiRequest;
use App\Http\Requests\Api\UpdateTransferDanaApiRequest;
use App\Http\Resources\TransferDanaResource;
use App\Models\TransferDana;
use App\Services\PermintaanDanaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferDanaController extends ApiController
{
    public function __construct(private readonly PermintaanDanaService $workflow) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = TransferDana::query()
            ->with(['opd', 'sumberDanaPengirim', 'sumberDanaPenerima'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->when($request->filled('opd_id') && $user->isAdmin(), fn ($q) => $q->where('opd_id', $request->input('opd_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
            ->when($request->filled('search'), fn ($q) => $q->where('nomor_transfer', 'like', '%'.$request->string('search').'%'))
            ->orderBy('created_at', 'desc');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(TransferDanaResource::collection($query->get())->resolve(), 'Data transfer dana berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), TransferDanaResource::class, 'Data transfer dana berhasil diambil.');
    }

    public function store(StoreTransferDanaApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        // Race-safe TF-XXXX/YYYY generation; status always starts as draft.
        $data['nomor_transfer'] = $this->workflow->nextNomorTransfer();
        $data['status'] = 'draft';

        $transfer = TransferDana::create($data);

        return $this->success(new TransferDanaResource($transfer->load(['opd', 'sumberDanaPengirim', 'sumberDanaPenerima'])), 'Transfer dana berhasil dibuat.', 201);
    }

    public function show(Request $request, TransferDana $transferDana): JsonResponse
    {
        $this->authorizeOpd($request, $transferDana->opd_id);

        $transferDana->load(['opd', 'sumberDanaPengirim', 'sumberDanaPenerima']);

        return $this->success(new TransferDanaResource($transferDana), 'Data transfer dana berhasil diambil.');
    }

    public function update(UpdateTransferDanaApiRequest $request, TransferDana $transferDana): JsonResponse
    {
        $this->authorizeOpd($request, $transferDana->opd_id);

        if ($transferDana->status === 'selesai') {
            return $this->businessError('Transfer dana tidak dapat diubah', [
                'status' => ['Transfer yang sudah selesai tidak dapat diubah.'],
            ]);
        }

        $data = $request->validated();

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        if (($data['status'] ?? null) === 'selesai' && $transferDana->status !== 'selesai') {
            $data['tanggal_selesai'] = now();
        }

        $transferDana->update($data);

        return $this->success(new TransferDanaResource($transferDana->fresh(['opd', 'sumberDanaPengirim', 'sumberDanaPenerima'])), 'Transfer dana berhasil diperbarui.');
    }

    public function destroy(Request $request, TransferDana $transferDana): JsonResponse
    {
        $this->authorizeOpd($request, $transferDana->opd_id);

        if ($transferDana->status === 'selesai') {
            return $this->businessError('Transfer dana tidak dapat dihapus', [
                'status' => ['Transfer yang sudah selesai tidak dapat dihapus.'],
            ]);
        }

        $transferDana->delete();

        return $this->success(message: 'Transfer dana berhasil dihapus.');
    }
}
