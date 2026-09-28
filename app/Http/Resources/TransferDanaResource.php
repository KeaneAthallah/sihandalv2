<?php

namespace App\Http\Resources;

use App\Models\TransferDana;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TransferDana
 */
class TransferDanaResource extends JsonResource
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
            'nomor_transfer' => $this->nomor_transfer,
            'opd_id' => $this->opd_id,
            'opd' => new OpdResource($this->whenLoaded('opd')),
            'jumlah' => (float) $this->jumlah,
            'sumber_dana' => $this->sumber_dana,
            'keterangan' => $this->keterangan,
            'status' => $this->status,
            'tanggal' => $this->tanggal?->toDateString(),
            'tanggal_selesai' => $this->tanggal_selesai?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
