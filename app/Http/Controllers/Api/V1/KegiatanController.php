<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreKegiatanApiRequest;
use App\Http\Requests\Api\UpdateKegiatanApiRequest;
use App\Http\Resources\KegiatanResource;
use App\Models\Kegiatan;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KegiatanController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Kegiatan::query()
            ->with(['program', 'opd'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->when($request->filled('program_id'), fn ($q) => $q->where('program_id', $request->input('program_id')))
            ->when($request->filled('opd_id') && $user->isAdmin(), fn ($q) => $q->where('opd_id', $request->input('opd_id')))
            ->when($request->filled('tahun_anggaran_id'), fn ($q) => $q->where('tahun_anggaran_id', $request->input('tahun_anggaran_id')))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('nama_kegiatan', 'like', $term)->orWhere('kode_kegiatan', 'like', $term));
            })
            ->orderBy('kode_kegiatan');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(KegiatanResource::collection($query->get())->resolve(), 'Data kegiatan berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), KegiatanResource::class, 'Data kegiatan berhasil diambil.');
    }

    public function store(StoreKegiatanApiRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['program_id'] = $request->input('program_id') ?? Program::query()->min('id');

        if ($data['program_id'] === null) {
            return $this->businessError('Program wajib dipilih.', ['program_id' => ['Program wajib dipilih.']]);
        }

        $realisasi = (float) ($data['realisasi'] ?? 0);
        $data['realisasi'] = $realisasi;
        $data['persentase'] = $data['pagu'] > 0 ? round($realisasi / $data['pagu'] * 100, 2) : 0;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $kegiatan = DB::transaction(fn () => Kegiatan::create($data));

        return $this->success(new KegiatanResource($kegiatan), 'Kegiatan berhasil ditambahkan.', 201);
    }

    public function show(Request $request, Kegiatan $kegiatan): JsonResponse
    {
        $this->authorizeOpd($request, $kegiatan->opd_id);

        $kegiatan->load(['program', 'opd']);

        return $this->success(new KegiatanResource($kegiatan), 'Data kegiatan berhasil diambil.');
    }

    public function update(UpdateKegiatanApiRequest $request, Kegiatan $kegiatan): JsonResponse
    {
        $this->authorizeOpd($request, $kegiatan->opd_id);

        $data = $request->validated();
        $realisasi = (float) ($data['realisasi'] ?? $kegiatan->realisasi);
        $data['realisasi'] = $realisasi;
        $data['persentase'] = $data['pagu'] > 0 ? round($realisasi / $data['pagu'] * 100, 2) : 0;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $kegiatan->update($data);

        return $this->success(new KegiatanResource($kegiatan->fresh()), 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Kegiatan $kegiatan): JsonResponse
    {
        $this->authorizeOpd($request, $kegiatan->opd_id);

        $hasFundedBelanjas = $kegiatan->subKegiatans()
            ->whereHas('belanjas', fn ($q) => $q->where('dana_di_commit', '>', 0)->orWhere('realisasi', '>', 0))
            ->exists();

        if ($hasFundedBelanjas) {
            return $this->businessError('Kegiatan tidak dapat dihapus', [
                'kegiatan' => ['Kegiatan memiliki belanja dengan dana berkomitmen/terealisasi sehingga tidak dapat dihapus.'],
            ]);
        }

        $kegiatan->delete();

        return $this->success(message: 'Kegiatan berhasil dihapus.');
    }
}
