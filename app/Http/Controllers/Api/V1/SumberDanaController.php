<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreSumberDanaApiRequest;
use App\Http\Resources\SumberDanaResource;
use App\Models\SumberDana;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SumberDanaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = SumberDana::query()
            ->when($request->filled('search'), fn ($q) => $q->where('nama_sumber_dana', 'like', '%'.$request->string('search').'%'))
            ->orderBy('nama_sumber_dana');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(SumberDanaResource::collection($query->get())->resolve(), 'Data sumber dana berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), SumberDanaResource::class, 'Data sumber dana berhasil diambil.');
    }

    public function show(SumberDana $sumberDana): JsonResponse
    {
        return $this->success(new SumberDanaResource($sumberDana), 'Data sumber dana berhasil diambil.');
    }

    public function store(StoreSumberDanaApiRequest $request): JsonResponse
    {
        $sumberDana = SumberDana::create($request->validated());

        return $this->success(new SumberDanaResource($sumberDana), 'Sumber dana berhasil ditambahkan.', 201);
    }

    public function update(StoreSumberDanaApiRequest $request, SumberDana $sumberDana): JsonResponse
    {
        $sumberDana->update($request->validated());

        return $this->success(new SumberDanaResource($sumberDana->fresh()), 'Sumber dana berhasil diperbarui.');
    }

    public function destroy(SumberDana $sumberDana): JsonResponse
    {
        $sumberDana->delete();

        return $this->success(message: 'Sumber dana berhasil dihapus.');
    }
}
