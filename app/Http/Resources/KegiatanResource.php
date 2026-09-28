<?php

namespace App\Http\Resources;

use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Kegiatan
 */
class KegiatanResource extends JsonResource
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
            'program_id' => $this->program_id,
            'program' => new ProgramResource($this->whenLoaded('program')),
            'opd_id' => $this->opd_id,
            'opd' => new OpdResource($this->whenLoaded('opd')),
            'sumber_dana_id' => $this->sumber_dana_id,
            'sumber_dana' => new SumberDanaResource($this->whenLoaded('sumberDana')),
            'rekening_id' => $this->rekening_id,
            'tahun_anggaran_id' => $this->tahun_anggaran_id,
            'kode_kegiatan' => $this->kode_kegiatan,
            'nama_kegiatan' => $this->nama_kegiatan,
            'kode_sub_kegiatan' => $this->kode_sub_kegiatan,
            'nama_sub_kegiatan' => $this->nama_sub_kegiatan,
            'kode_rekening' => $this->kode_rekening,
            'nama_rekening' => $this->nama_rekening,
            'pagu' => (float) $this->pagu,
            'realisasi' => (float) $this->realisasi,
            'persentase' => (float) $this->persentase,
            'sub_kegiatans_count' => $this->whenCounted('subKegiatans'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
