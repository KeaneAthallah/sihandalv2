<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreRekeningApiRequest;
use App\Http\Requests\Api\UpdateRekeningApiRequest;
use App\Http\Resources\RekeningResource;
use App\Models\Rekening;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RekeningController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Rekening::query()
            ->with('parent')
            ->withCount('children')
            ->when($request->filled('tipe'), fn ($q) => $q->where('tipe', $request->input('tipe')))
            ->when($request->filled('parent_id'), fn ($q) => $q->where('parent_id', $request->input('parent_id')))
            ->when($request->boolean('kas_leaf'), fn ($q) => $q->kasLeaves())
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('nama', 'like', $term)->orWhere('kode', 'like', $term));
            })
            ->orderBy('kode');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(RekeningResource::collection($query->get())->resolve(), 'Data rekening berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), RekeningResource::class, 'Data rekening berhasil diambil.');
    }

    public function show(Rekening $rekening): JsonResponse
    {
        $rekening->load(['parent'])->loadCount('children');

        return $this->success(new RekeningResource($rekening), 'Data rekening berhasil diambil.');
    }

    public function store(StoreRekeningApiRequest $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $rekening = Rekening::create($request->validated());

        return $this->success(new RekeningResource($rekening->loadCount('children')), 'Rekening berhasil ditambahkan.', 201);
    }

    public function update(UpdateRekeningApiRequest $request, Rekening $rekening): JsonResponse
    {
        $this->authorizeAdmin($request);

        $rekening->update($request->validated());

        return $this->success(new RekeningResource($rekening->fresh('parent')->loadCount('children')), 'Rekening berhasil diperbarui.');
    }

    public function destroy(Request $request, Rekening $rekening): JsonResponse
    {
        $this->authorizeAdmin($request);

        if ($rekening->children()->exists()) {
            return $this->businessError('Rekening tidak dapat dihapus', [
                'rekening' => ['Rekening memiliki rekening detail sehingga tidak dapat dihapus.'],
            ]);
        }

        $rekening->delete();

        return $this->success(message: 'Rekening berhasil dihapus.');
    }

    private function authorizeAdmin(Request $request): void
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengelola rekening kas.');
        }
    }
}
