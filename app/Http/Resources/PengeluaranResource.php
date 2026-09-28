<?php

namespace App\Http\Resources;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Pengeluaran
 */
class PengeluaranResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'opd_id' => $this->opd_id,
            'opd' => new OpdResource($this->whenLoaded('opd')),
            'rekening_id' => $this->rekening_id,
            'rekening' => new RekeningResource($this->whenLoaded('rekening')),
            'kegiatan_id' => $this->kegiatan_id,
            'kegiatan' => new KegiatanResource($this->whenLoaded('kegiatan')),
            'sub_kegiatan_id' => $this->sub_kegiatan_id,
            'belanja_id' => $this->belanja_id,
            'sumber_dana_id' => $this->sumber_dana_id,
            'sumber_dana' => new SumberDanaResource($this->whenLoaded('sumberDana')),
            'tahun_anggaran_id' => $this->tahun_anggaran_id,
            'kode_kegiatan' => $this->kode_kegiatan,
            'nama_kegiatan' => $this->nama_kegiatan,
            'sumber_dana' => $this->sumber_dana,
            'anggaran' => (float) $this->anggaran,
            'realisasi' => (float) $this->realisasi,
            'persentase' => (float) $this->persentase,
            'tanggal' => $this->tanggal?->toDateString(),
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
