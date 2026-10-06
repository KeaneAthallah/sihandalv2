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
            'sumber_dana_pengirim_id' => $this->sumber_dana_pengirim_id,
            'sumber_dana_pengirim' => new SumberDanaResource($this->whenLoaded('sumberDanaPengirim')),
            'sumber_dana_penerima_id' => $this->sumber_dana_penerima_id,
            'sumber_dana_penerima' => new SumberDanaResource($this->whenLoaded('sumberDanaPenerima')),
            'keterangan' => $this->keterangan,
            'status' => $this->status,
            'tanggal' => $this->tanggal?->toDateString(),
            'tanggal_selesai' => $this->tanggal_selesai?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
