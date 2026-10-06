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
            'sub_kegiatan' => new SubKegiatanResource($this->whenLoaded('subKegiatan')),
            'belanja_id' => $this->belanja_id,
            'belanja' => new BelanjaResource($this->whenLoaded('belanja')),
            'sumber_dana_id' => $this->sumber_dana_id,
            'sumber_dana_ref' => new SumberDanaResource($this->whenLoaded('sumberDana')),
            'sumber_dana' => $this->sumber_dana,
            'tahun_anggaran_id' => $this->tahun_anggaran_id,
            'jumlah' => (float) $this->jumlah,
            'keperluan' => $this->keperluan,
            'no_sp2d' => $this->no_sp2d,
            'tanggal_sp2d' => $this->tanggal_sp2d?->toDateString(),
            'tanggal' => $this->tanggal?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
