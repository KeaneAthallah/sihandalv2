<?php

namespace App\Http\Resources;

use App\Models\SubKegiatan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SubKegiatan
 */
class SubKegiatanResource extends JsonResource
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
            'kegiatan_id' => $this->kegiatan_id,
            'kegiatan' => new KegiatanResource($this->whenLoaded('kegiatan')),
            'kode_sub_kegiatan' => $this->kode_sub_kegiatan,
            'nama_sub_kegiatan' => $this->nama_sub_kegiatan,
            'pagu' => (float) $this->pagu,
            'realisasi' => (float) $this->realisasi,
            'persentase' => (float) $this->persentase,
            'belanjas_count' => $this->whenCounted('belanjas'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
