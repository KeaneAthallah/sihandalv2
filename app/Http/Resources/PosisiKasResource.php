<?php

namespace App\Http\Resources;

use App\Models\PosisiKas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PosisiKas
 */
class PosisiKasResource extends JsonResource
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
            'tanggal' => $this->tanggal?->toDateString(),
            'nama_rekening' => $this->nama_rekening,
            'nomor_rekening' => $this->nomor_rekening,
            'saldo' => (float) $this->saldo,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
