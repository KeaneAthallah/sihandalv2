<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\StorePengeluaranApiRequest;
use App\Http\Resources\PengeluaranResource;
use App\Models\Kegiatan;
use App\Models\Pengeluaran;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengeluaranController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Pengeluaran::query()
            ->with(['opd', 'kegiatan', 'sumberDana', 'rekening'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('opd_id', $user->opd_id))
            ->when($request->filled('opd_id') && $user->isAdmin(), fn ($q) => $q->where('opd_id', $request->input('opd_id')))
            ->when($request->filled('kegiatan_id'), fn ($q) => $q->where('kegiatan_id', $request->input('kegiatan_id')))
            ->when($request->filled('sub_kegiatan_id'), fn ($q) => $q->where('sub_kegiatan_id', $request->input('sub_kegiatan_id')))
            ->when($request->filled('belanja_id'), fn ($q) => $q->where('belanja_id', $request->input('belanja_id')))
            ->when($request->filled('sumber_dana_id'), fn ($q) => $q->where('sumber_dana_id', $request->input('sumber_dana_id')))
            ->when($request->filled('rekening_id'), fn ($q) => $q->where('rekening_id', $request->input('rekening_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->input('tanggal_sampai')))
            ->when($request->filled('search'), fn ($q) => $q->where('nama_kegiatan', 'like', '%'.$request->string('search').'%'));

        $perPage = $this->perPage($request);

        if ($perPage === null) {
            return $this->success(PengeluaranResource::collection($query->orderBy('tanggal', 'desc')->get())->resolve(), 'Data pengeluaran berhasil diambil.');
        }

        return $this->paginated($query->orderBy('tanggal', 'desc')->paginate($perPage), PengeluaranResource::class, 'Data pengeluaran berhasil diambil.');
    }

    public function store(StorePengeluaranApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        $pengeluaran = DB::transaction(function () use ($request, $data): Pengeluaran {
            $data['tahun_anggaran_id'] = $data['tahun_anggaran_id'] ?? TahunAnggaran::currentActive()?->id;
            $data['persentase'] = $data['anggaran'] > 0 ? round(($data['realisasi'] ?? 0) / $data['anggaran'] * 100, 2) : 0;

            if (($data['kegiatan_id'] ?? null) !== null) {
                $kegiatan = Kegiatan::find($data['kegiatan_id']);
                $data['kode_kegiatan'] = $kegiatan?->kode_kegiatan;
                $data['nama_kegiatan'] = $kegiatan?->nama_kegiatan;
            }

            if (($data['sumber_dana_id'] ?? null) !== null) {
                $data['sumber_dana'] = SumberDana::find($data['sumber_dana_id'])?->nama_sumber_dana;
            }

            if (! $request->user()->isAdmin()) {
                $data['opd_id'] = $request->user()->opd_id;
            }

            return Pengeluaran::create($data);
        });

        return $this->success(new PengeluaranResource($pengeluaran->fresh(['opd', 'kegiatan', 'sumberDana', 'rekening'])), 'Pengeluaran berhasil ditambahkan.', 201);
    }

    public function show(Request $request, Pengeluaran $pengeluaran): JsonResponse
    {
        $this->authorizeOpd($request, $pengeluaran->opd_id);

        $pengeluaran->load(['opd', 'kegiatan', 'sumberDana', 'rekening']);

        return $this->success(new PengeluaranResource($pengeluaran), 'Data pengeluaran berhasil diambil.');
    }

    public function update(StorePengeluaranApiRequest $request, Pengeluaran $pengeluaran): JsonResponse
    {
        $this->authorizeOpd($request, $pengeluaran->opd_id);

        $data = $request->validated();

        DB::transaction(function () use ($data, $pengeluaran): void {
            $data['persentase'] = $data['anggaran'] > 0 ? round(($data['realisasi'] ?? 0) / $data['anggaran'] * 100, 2) : 0;

            if (($data['kegiatan_id'] ?? null) !== null) {
                $kegiatan = Kegiatan::find($data['kegiatan_id']);
                $data['kode_kegiatan'] = $kegiatan?->kode_kegiatan;
                $data['nama_kegiatan'] = $kegiatan?->nama_kegiatan;
            }

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
