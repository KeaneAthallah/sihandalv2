<?php

namespace App\Http\Resources;

use App\Models\Belanja;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lightweight Belanja representation for collections; the detailed variant
 * (BelanjaDetailResource) adds the financial breakdown and relationships.
 *
 * @mixin Belanja
 */
class BelanjaResource extends JsonResource
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
            'sub_kegiatan_id' => $this->sub_kegiatan_id,
            'rekening_id' => $this->rekening_id,
            'sumber_dana_id' => $this->sumber_dana_id,
            'opd_id' => $this->opd_id,
            'tahun_anggaran_id' => $this->tahun_anggaran_id,
            'pagu' => (float) $this->pagu,
            'realisasi' => (float) $this->realisasi,
            'dana_di_commit' => (float) $this->dana_di_commit,
            'available_pagu' => $this->when(
                $this->resource instanceof Belanja,
                fn () => $this->availablePagu(),
            ),
            'rekening' => new RekeningResource($this->whenLoaded('rekening')),
            'sumber_dana' => new SumberDanaResource($this->whenLoaded('sumberDana')),
            'sub_kegiatan' => new SubKegiatanResource($this->whenLoaded('subKegiatan')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
