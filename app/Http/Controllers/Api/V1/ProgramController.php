<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreProgramApiRequest;
use App\Http\Requests\Api\UpdateProgramApiRequest;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Program::query()
            ->with(['opd'])
            ->withCount('kegiatans')
            ->when($request->filled('opd_id'), fn ($q) => $q->where('opd_id', $request->input('opd_id')))
            ->when($request->filled('tahun_anggaran_id'), fn ($q) => $q->where('tahun_anggaran_id', $request->input('tahun_anggaran_id')))
            ->when($request->filled('search'), function ($q) use ($request): void {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($w) => $w->where('nama_program', 'like', $term)->orWhere('kode_program', 'like', $term));
            });

        // Programs may be global (opd_id null); OPD users are additionally
        // constrained through their kegiatan rows, matching web behavior.
        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user): void {
                $q->whereNull('opd_id')
                    ->orWhere('opd_id', $user->opd_id)
                    ->orWhereHas('kegiatans', fn ($k) => $k->where('opd_id', $user->opd_id));
            });
        }

        $query->orderBy('kode_program');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(ProgramResource::collection($query->get())->resolve(), 'Data program berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), ProgramResource::class, 'Data program berhasil diambil.');
    }

    public function store(StoreProgramApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $request->user()->isAdmin() && empty($data['opd_id'])) {
            $data['opd_id'] = $request->user()->opd_id;
        }

        $program = Program::create($data);

        return $this->success(new ProgramResource($program), 'Program berhasil ditambahkan.', 201);
    }

    public function show(Request $request, Program $program): JsonResponse
    {
        $this->authorizeProgram($request->user(), $program);

        $program->load(['opd'])->loadCount('kegiatans');

        return $this->success(new ProgramResource($program), 'Data program berhasil diambil.');
    }

    public function update(UpdateProgramApiRequest $request, Program $program): JsonResponse
    {
        $this->authorizeProgram($request->user(), $program);

        $program->update($request->validated());

        return $this->success(new ProgramResource($program->fresh()), 'Program berhasil diperbarui.');
    }

    public function destroy(Request $request, Program $program): JsonResponse
    {
        $this->authorizeProgram($request->user(), $program);

        $hasFundedBelanjas = $program->kegiatans()
            ->whereHas('subKegiatans.belanjas', fn ($q) => $q->where('dana_di_commit', '>', 0)->orWhere('realisasi', '>', 0))
            ->exists();

        if ($hasFundedBelanjas) {
            return $this->businessError('Program tidak dapat dihapus', [
                'program' => ['Program memiliki belanja dengan dana berkomitmen/terealisasi sehingga tidak dapat dihapus.'],
            ]);
        }

        $program->delete();

        return $this->success(message: 'Program berhasil dihapus.');
    }

    /**
     * Same authorization rule as the web ProgramKegiatanController.
     */
    private function authorizeProgram(User $user, Program $program): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $belongsToOtherOpd = $program->kegiatans()->where('opd_id', '!=', $user->opd_id)->exists();

        if ($belongsToOtherOpd) {
            abort(403, 'Unauthorized');
        }

        if ($program->opd_id !== null && (int) $program->opd_id !== (int) $user->opd_id) {
            abort(403, 'Unauthorized');
        }
    }
}
