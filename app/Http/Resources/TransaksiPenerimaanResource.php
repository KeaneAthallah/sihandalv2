<?php

namespace App\Http\Resources;

use App\Models\TransaksiPenerimaan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TransaksiPenerimaan
 */
class TransaksiPenerimaanResource extends JsonResource
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
            'penerimaan_id' => $this->penerimaan_id,
            'nomor_registrasi' => $this->nomor_registrasi,
            'realisasi' => (float) $this->realisasi,
            'tanggal' => $this->tanggal?->toDateString(),
            'keterangan' => $this->keterangan,
            'penerimaan' => new PenerimaanResource($this->whenLoaded('penerimaan')),
            'bkus' => TransaksiPenerimaanBkuResource::collection($this->whenLoaded('bkus')),
            'total_bku' => $this->when(
                $this->relationLoaded('bkus'),
                fn () => $this->totalBku(),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
