<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreSubKegiatanApiRequest;
use App\Http\Requests\Api\UpdateSubKegiatanApiRequest;
use App\Http\Resources\SubKegiatanResource;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubKegiatanController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = SubKegiatan::query()
            ->with(['kegiatan'])
            ->when($request->filled('kegiatan_id'), fn ($q) => $q->where('kegiatan_id', $request->input('kegiatan_id')))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('nama_sub_kegiatan', 'like', $term)->orWhere('kode_sub_kegiatan', 'like', $term));
            })
            ->whereHas('kegiatan', fn ($k) => $user->isAdmin() ? $k : $k->where('opd_id', $user->opd_id))
            ->orderBy('kode_sub_kegiatan');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(SubKegiatanResource::collection($query->get())->resolve(), 'Data sub kegiatan berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), SubKegiatanResource::class, 'Data sub kegiatan berhasil diambil.');
    }

    public function store(StoreSubKegiatanApiRequest $request): JsonResponse
    {
        $kegiatanId = $request->input('kegiatan_id');
        $kegiatan = Kegiatan::find($kegiatanId);

        if ($kegiatan === null) {
            return $this->businessError('Kegiatan wajib dipilih.', ['kegiatan_id' => ['Kegiatan wajib dipilih.']]);
        }

        $this->authorizeOpd($request, $kegiatan->opd_id);

        $data = $request->validated();
        $realisasi = (float) ($data['realisasi'] ?? 0);
        $data['realisasi'] = $realisasi;
        $data['persentase'] = $data['pagu'] > 0 ? round($realisasi / $data['pagu'] * 100, 2) : 0;
        $data['kegiatan_id'] = $kegiatan->id;

        $subKegiatan = DB::transaction(fn () => SubKegiatan::create($data));

        return $this->success(new SubKegiatanResource($subKegiatan), 'Sub kegiatan berhasil ditambahkan.', 201);
    }

    public function show(Request $request, SubKegiatan $subKegiatan): JsonResponse
    {
        $this->authorizeOpd($request, $subKegiatan->kegiatan?->opd_id);

        $subKegiatan->load(['kegiatan']);

        return $this->success(new SubKegiatanResource($subKegiatan), 'Data sub kegiatan berhasil diambil.');
    }

    public function update(UpdateSubKegiatanApiRequest $request, SubKegiatan $subKegiatan): JsonResponse
    {
        $this->authorizeOpd($request, $subKegiatan->kegiatan?->opd_id);

        $data = $request->validated();
        $realisasi = (float) ($data['realisasi'] ?? $subKegiatan->realisasi);
        $data['realisasi'] = $realisasi;
        $data['persentase'] = $data['pagu'] > 0 ? round($realisasi / $data['pagu'] * 100, 2) : 0;

        $subKegiatan->update($data);

        return $this->success(new SubKegiatanResource($subKegiatan->fresh()), 'Sub kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, SubKegiatan $subKegiatan): JsonResponse
    {
        $this->authorizeOpd($request, $subKegiatan->kegiatan?->opd_id);

        $hasCommittedFunds = $subKegiatan->belanjas()
            ->where(fn ($q) => $q->where('dana_di_commit', '>', 0)->orWhere('realisasi', '>', 0))
            ->exists();

        if ($hasCommittedFunds) {
            return $this->businessError('Sub kegiatan tidak dapat dihapus', [
                'sub_kegiatan' => ['Sub kegiatan memiliki belanja dengan dana berkomitmen/terealisasi sehingga tidak dapat dihapus.'],
            ]);
        }

        $subKegiatan->delete();

        return $this->success(message: 'Sub kegiatan berhasil dihapus.');
    }
}
