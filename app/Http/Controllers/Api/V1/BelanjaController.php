<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreBelanjaApiRequest;
use App\Http\Requests\Api\StoreBelanjaFinancialActionRequest;
use App\Http\Resources\BelanjaResource;
use App\Models\Belanja;
use App\Models\TahunAnggaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BelanjaController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Belanja::query()
            ->with(['rekening', 'sumberDana', 'subKegiatan.kegiatan'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->when($request->filled('opd_id') && $user->isAdmin(), fn ($q) => $q->where('opd_id', $request->input('opd_id')))
            ->when($request->filled('sub_kegiatan_id'), fn ($q) => $q->where('sub_kegiatan_id', $request->input('sub_kegiatan_id')))
            ->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('tahun_anggaran_id'), fn ($q) => $q->where('tahun_anggaran_id', $request->input('tahun_anggaran_id')))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%'.$request->string('search').'%';
                $q->whereHas('rekening', fn ($r) => $r->where('nama', 'like', $term)->orWhere('kode', 'like', $term));
            });

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(BelanjaResource::collection($query->get())->resolve(), 'Data belanja berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), BelanjaResource::class, 'Data belanja berhasil diambil.');
    }

    public function store(StoreBelanjaApiRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['sub_kegiatan_id'] = $request->input('sub_kegiatan_id');
        $data['dana_di_commit'] = 0;
        $data['tahun_anggaran_id'] = $request->input('tahun_anggaran_id') ?? TahunAnggaran::currentActive()?->id;

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $belanja = DB::transaction(fn () => Belanja::create($data));

        return $this->success(new BelanjaResource($belanja->fresh(['rekening', 'sumberDana', 'subKegiatan'])), 'Belanja berhasil ditambahkan.', 201);
    }

    public function show(Request $request, Belanja $belanja): JsonResponse
    {
        $this->authorizeOpd($request, $belanja->opd_id);

        $belanja->load(['rekening', 'sumberDana', 'subKegiatan.kegiatan']);

        return $this->success(new BelanjaResource($belanja), 'Data belanja berhasil diambil.');
    }

    public function update(Request $request, Belanja $belanja): JsonResponse
    {
        $this->authorizeOpd($request, $belanja->opd_id);

        $data = $request->validate([
            'rekening_id' => ['required', 'exists:rekenings,id'],
            'sumber_dana_id' => ['nullable', 'exists:sumber_danas,id'],
            'pagu' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['nullable', 'numeric', 'min:0'],
        ]);

        $newRealisasi = (float) ($data['realisasi'] ?? $belanja->realisasi);

        // Historical realization can never be reduced.
        if ($newRealisasi < (float) $belanja->realisasi) {
            return $this->businessError('Data belanja tidak valid', [
                'realisasi' => ['Realisasi tidak boleh dikurangi dari nilai yang telah ditetapkan.'],
            ]);
        }

        $minimum = round((float) $belanja->dana_di_commit + $newRealisasi, 2);

        if ((float) $data['pagu'] < $minimum) {
            return $this->businessError('Data belanja tidak valid', [
                'pagu' => ['Pagu tidak boleh kurang dari total dana commit dan realisasi.'],
            ]);
        }

        if (! $request->user()->isAdmin()) {
            $data['opd_id'] = $request->user()->opd_id;
        } else {
            $data['opd_id'] = $belanja->opd_id;
        }

        $belanja->update($data);

        return $this->success(new BelanjaResource($belanja->fresh()), 'Belanja berhasil diperbarui.');
    }

    /**
     * Explicit commit action: delegates to Belanja::commit() so pagu/commit
     * invariants are enforced by the domain, with row locking + transaction.
     */
    public function commit(StoreBelanjaFinancialActionRequest $request, Belanja $belanja): JsonResponse
    {
        $this->authorizeOpd($request, $belanja->opd_id);

        try {
            $belanja->commit((float) $request->input('amount'));
        } catch (RuntimeException $e) {
            return $this->businessError('Unable to perform operation', ['business' => [$e->getMessage()]]);
        }

        return $this->success(new BelanjaResource($belanja->fresh()), 'Dana berhasil di-commit.');
    }

    /**
     * Explicit release action: delegates to Belanja::releaseCommit().
     */
    public function release(StoreBelanjaFinancialActionRequest $request, Belanja $belanja): JsonResponse
    {
        $this->authorizeOpd($request, $belanja->opd_id);

        try {
            $belanja->releaseCommit((float) $request->input('amount'));
        } catch (RuntimeException $e) {
            return $this->businessError('Unable to perform operation', ['business' => [$e->getMessage()]]);
        }

        return $this->success(new BelanjaResource($belanja->fresh()), 'Dana commit berhasil dilepas.');
    }

    /**
     * Explicit realize action: delegates to Belanja::realize() which enforces
     * the availablePagu invariant.
     */
    public function realize(StoreBelanjaFinancialActionRequest $request, Belanja $belanja): JsonResponse
    {
        $this->authorizeOpd($request, $belanja->opd_id);

        try {
            $belanja->realize((float) $request->input('amount'));
        } catch (RuntimeException $e) {
            return $this->businessError('Unable to perform operation', ['business' => [$e->getMessage()]]);
        }

        return $this->success(new BelanjaResource($belanja->fresh()), 'Realisasi berhasil dicatat.');
    }
}
