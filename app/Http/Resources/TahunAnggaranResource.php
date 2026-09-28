<?php

namespace App\Http\Resources;

use App\Models\TahunAnggaran;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TahunAnggaran
 */
class TahunAnggaranResource extends JsonResource
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
            'tahun' => $this->tahun,
            'tanggal_mulai' => $this->tanggal_mulai?->toDateString(),
            'tanggal_selesai' => $this->tanggal_selesai?->toDateString(),
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_open' => $this->isOpen(),
        ];
    }
}
