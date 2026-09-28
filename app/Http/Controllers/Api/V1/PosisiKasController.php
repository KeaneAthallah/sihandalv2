<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StorePosisiKasApiRequest;
use App\Http\Resources\PosisiKasResource;
use App\Models\PosisiKas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosisiKasController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PosisiKas::query()
            ->with(['opd', 'rekening'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->when($request->filled('opd_id') && $user->isAdmin(), fn ($q) => $q->where('opd_id', $request->input('opd_id')))
            ->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
            ->orderBy('tanggal', 'desc');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(PosisiKasResource::collection($query->get())->resolve(), 'Data posisi kas berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), PosisiKasResource::class, 'Data posisi kas berhasil diambil.');
    }

    public function store(StorePosisiKasApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        // saldo_akhir is always computed server-side.
        $data['saldo_akhir'] = ($data['saldo_awal'] ?? 0) + ($data['penerimaan'] ?? 0) - ($data['pengeluaran'] ?? 0);

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $posisiKas = PosisiKas::create($data);

        return $this->success(new PosisiKasResource($posisiKas->load(['opd', 'rekening'])), 'Posisi kas berhasil ditambahkan.', 201);
    }

    public function show(Request $request, PosisiKas $posisiKas): JsonResponse
    {
        $this->authorizeOpd($request, $posisiKas->opd_id);

        $posisiKas->load(['opd', 'rekening']);

        return $this->success(new PosisiKasResource($posisiKas), 'Data posisi kas berhasil diambil.');
    }

    public function update(StorePosisiKasApiRequest $request, PosisiKas $posisiKas): JsonResponse
    {
        $this->authorizeOpd($request, $posisiKas->opd_id);

        $data = $request->validated();
        $data['saldo_akhir'] = ($data['saldo_awal'] ?? 0) + ($data['penerimaan'] ?? 0) - ($data['pengeluaran'] ?? 0);

        $posisiKas->update($data);

        return $this->success(new PosisiKasResource($posisiKas->fresh(['opd', 'rekening'])), 'Posisi kas berhasil diperbarui.');
    }
}
