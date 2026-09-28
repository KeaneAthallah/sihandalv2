<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StoreTahunAnggaranApiRequest;
use App\Http\Requests\Api\UpdateTahunAnggaranApiRequest;
use App\Http\Resources\TahunAnggaranResource;
use App\Models\AuditLog;
use App\Models\TahunAnggaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAnggaranController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = TahunAnggaran::query()
            ->orderBy('tahun', 'desc');

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(TahunAnggaranResource::collection($query->get())->resolve(), 'Data tahun anggaran berhasil diambil.');
        }

        return $this->paginated($query->paginate($perPage), TahunAnggaranResource::class, 'Data tahun anggaran berhasil diambil.');
    }

    /**
     * The currently active fiscal year (available to any authenticated user).
     */
    public function active(): JsonResponse
    {
        $tahun = TahunAnggaran::query()->active()->first();

        if ($tahun === null) {
            return $this->success(null, 'Belum ada tahun anggaran aktif.');
        }

        return $this->success(new TahunAnggaranResource($tahun), 'Tahun anggaran aktif berhasil diambil.');
    }

    public function store(StoreTahunAnggaranApiRequest $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $tahun = DB::transaction(function () use ($request): TahunAnggaran {
            $tahun = TahunAnggaran::create([
                'tahun' => $request->input('tahun'),
                'tanggal_mulai' => $request->input('tanggal_mulai'),
                'tanggal_selesai' => $request->input('tanggal_selesai'),
                'status' => 'open',
                'is_active' => false,
            ]);

            AuditLog::log('created', $tahun, [], $tahun->toArray());

            return $tahun;
        });

        return $this->success(new TahunAnggaranResource($tahun), 'Tahun anggaran berhasil ditambahkan.', 201);
    }

    public function update(UpdateTahunAnggaranApiRequest $request, TahunAnggaran $tahunAnggaran): JsonResponse
    {
        $this->authorizeAdmin($request);

        DB::transaction(function () use ($request, $tahunAnggaran): void {
            $old = $tahunAnggaran->toArray();

            if ($request->input('status') === 'closed') {
                $tahunAnggaran->close();
            } else {
                $tahunAnggaran->open();
            }

            AuditLog::log(
                $request->input('status') === 'closed' ? 'period_closed' : 'period_reopened',
                $tahunAnggaran,
                $old,
                $tahunAnggaran->fresh()->toArray(),
            );
        });

        return $this->success(new TahunAnggaranResource($tahunAnggaran->fresh()), 'Status tahun anggaran berhasil diperbarui.');
    }

    public function activate(Request $request, TahunAnggaran $tahunAnggaran): JsonResponse
    {
        $this->authorizeAdmin($request);

        DB::transaction(function () use ($tahunAnggaran): void {
            $old = $tahunAnggaran->toArray();
            $tahunAnggaran->activate();
            AuditLog::log('updated', $tahunAnggaran, $old, $tahunAnggaran->fresh()->toArray());
        });

        return $this->success(new TahunAnggaranResource($tahunAnggaran->fresh()), "Tahun anggaran {$tahunAnggaran->tahun} diaktifkan.");
    }

    private function authorizeAdmin(Request $request): void
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat mengelola tahun anggaran.');
        }
    }
}
