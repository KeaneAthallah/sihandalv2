<?php

namespace App\Http\Resources;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Program
 */
class ProgramResource extends JsonResource
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
            'kode_program' => $this->kode_program,
            'nama_program' => $this->nama_program,
            'opd' => new OpdResource($this->whenLoaded('opd')),
            'opd_id' => $this->opd_id,
            'tahun_anggaran_id' => $this->tahun_anggaran_id,
            'tahun_anggaran' => new TahunAnggaranResource($this->whenLoaded('tahunAnggaran')),
            'kegiatans_count' => $this->whenCounted('kegiatans'),
            // Program totals are derived from kegiatan rows, never persisted here.
            'total_pagu_kegiatan' => $this->when(isset($this->total_pagu_kegiatan), fn () => (float) $this->total_pagu_kegiatan),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
