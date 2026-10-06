<?php

namespace App\Http\Resources;

use App\Models\PermintaanDana;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PermintaanDana
 */
class PermintaanDanaResource extends JsonResource
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
            'nomor_permintaan' => $this->nomor_permintaan,
            'opd_id' => $this->opd_id,
            'opd' => new OpdResource($this->whenLoaded('opd')),
            'sumber_dana_id' => $this->sumber_dana_id,
            'sumber_dana_ref' => new SumberDanaResource($this->whenLoaded('sumberDana')),
            'sumber_dana' => $this->sumber_dana,
            'kegiatan_id' => $this->kegiatan_id,
            'kegiatan' => new KegiatanResource($this->whenLoaded('kegiatan')),
            'sub_kegiatan_id' => $this->sub_kegiatan_id,
            'sub_kegiatan' => new SubKegiatanResource($this->whenLoaded('subKegiatan')),
            'belanja_id' => $this->belanja_id,
            'belanja' => new BelanjaResource($this->whenLoaded('belanja')),
            'rekening_id' => $this->rekening_id,
            'rekening' => new RekeningResource($this->whenLoaded('rekening')),
            'tahun_anggaran_id' => $this->tahun_anggaran_id,
            'jumlah' => (float) $this->jumlah,
            'keperluan' => $this->keperluan,
            'status' => $this->status,
            'tanggal' => $this->tanggal?->toDateString(),
            'tanggal_disetujui' => $this->tanggal_disetujui?->toDateString(),
            'persetujuans' => PersetujuanResource::collection($this->whenLoaded('persetujuans')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
