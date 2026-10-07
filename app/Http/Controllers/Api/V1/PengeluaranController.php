<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StorePengeluaranApiRequest;
use App\Http\Resources\PengeluaranResource;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use App\Services\PermintaanDanaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PengeluaranController extends ApiController
{
    public function __construct(private readonly PermintaanDanaService $permintaanDanaService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Pengeluaran::query()
            ->with(['opd', 'kegiatan', 'sumberDana', 'rekening', 'permintaanDana'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->when($request->filled('opd_id') && $user->isAdmin(), fn ($q) => $q->where('opd_id', $request->input('opd_id')))
            ->when($request->filled('kegiatan_id'), fn ($q) => $q->where('kegiatan_id', $request->input('kegiatan_id')))
            ->when($request->filled('sub_kegiatan_id'), fn ($q) => $q->where('sub_kegiatan_id', $request->input('sub_kegiatan_id')))
            ->when($request->filled('belanja_id'), fn ($q) => $q->where('belanja_id', $request->input('belanja_id')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
            ->when($request->filled('search'), fn ($q) => $q->where('keperluan', 'like', '%'.$request->string('search').'%'));

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(PengeluaranResource::collection($query->orderBy('tanggal', 'desc')->get())->resolve(), 'Data pengeluaran berhasil diambil.');
        }

        return $this->paginated($query->orderBy('tanggal', 'desc')->paginate($perPage), PengeluaranResource::class, 'Data pengeluaran berhasil diambil.');
    }

    public function store(StorePengeluaranApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Mode dari permintaan dana (admin): seluruh
        // field keuangan di-copy dari permintaan dana
        // di server, hanya SP2D yang diinput.
        if (! empty($data['permintaan_dana_id'])) {
            if (! $request->user()->isAdmin()) {
                return $this->businessError('Unauthorized', [
                    'permintaan_dana_id' => ['Hanya admin yang dapat membuat pengeluaran dari permintaan dana.'],
                ]);
            }

            try {
                $permintaanDana = PermintaanDana::findOrFail($data['permintaan_dana_id']);

                $pengeluaran = $this->permintaanDanaService->catatPengeluaran(
                    $permintaanDana,
                    $data['no_sp2d'] ?? null,
                    $data['tanggal_sp2d'] ?? null,
                    $data['tanggal'] ?? null,
                );

                return $this->success(
                    new PengeluaranResource($pengeluaran->load(['opd', 'kegiatan', 'sumberDana', 'rekening', 'permintaanDana'])),
                    'Pengeluaran dari permintaan dana berhasil ditambahkan.',
                    201,
                );
            } catch (RuntimeException $e) {
                return $this->businessError('Unable to create expenditure', ['business' => [$e->getMessage()]]);
            }
        }

        // Mode manual
        $pengeluaran = DB::transaction(function () use ($request, $data): Pengeluaran {
            $data['tahun_anggaran_id'] = $data['tahun_anggaran_id'] ?? TahunAnggaran::currentActive()?->id;

            if (($data['sumber_dana_id'] ?? null) !== null) {
                $data['sumber_dana'] = SumberDana::find($data['sumber_dana_id'])?->nama_sumber_dana;
            }

            if (! $request->user()->isAdmin()) {
                $data['opd_id'] = $request->user()->opd_id;
            }

            return Pengeluaran::create($data);
        });

        return $this->success(
            new PengeluaranResource($pengeluaran->fresh(['opd', 'kegiatan', 'sumberDana', 'rekening'])),
            'Pengeluaran berhasil ditambahkan.',
            201,
        );
    }

    public function show(Request $request, Pengeluaran $pengeluaran): JsonResponse
    {
        $this->authorizeOpd($request, $pengeluaran->opd_id);

        $pengeluaran->load(['opd', 'kegiatan', 'sumberDana', 'rekening', 'permintaanDana']);

        return $this->success(new PengeluaranResource($pengeluaran), 'Data pengeluaran berhasil diambil.');
    }

    public function update(StorePengeluaranApiRequest $request, Pengeluaran $pengeluaran): JsonResponse
    {
        $this->authorizeOpd($request, $pengeluaran->opd_id);

        $data = $request->validated();

        // Kaitan permintaan dana hanya dibentuk saat
        // pembuatan dan tidak dapat diubah via edit.
        unset($data['permintaan_dana_id']);

        DB::transaction(function () use ($data, $pengeluaran): void {
            if (($data['sumber_dana_id'] ?? null) !== null) {
                $data['sumber_dana'] = SumberDana::find($data['sumber_dana_id'])?->nama_sumber_dana;
            }

            $pengeluaran->update($data);
        });

        return $this->success(new PengeluaranResource($pengeluaran->fresh(['opd', 'kegiatan', 'sumberDana', 'rekening'])), 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Request $request, Pengeluaran $pengeluaran): JsonResponse
    {
        $this->authorizeOpd($request, $pengeluaran->opd_id);

        $pengeluaran->delete();

        return $this->success(message: 'Pengeluaran berhasil dihapus.');
    }
}
