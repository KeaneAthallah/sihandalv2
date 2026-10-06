<?php

namespace App\Http\Resources;

use App\Models\Penerimaan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Penerimaan master resource. Realization is never persisted on the
 * master: target/realisasi/persentase are computed here from
 * transaction aggregation.
 *
 * @mixin Penerimaan
 */
class PenerimaanResource extends JsonResource
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
            'sub_rekening_id' => $this->sub_rekening_id,
            'sub_rekening' => new RekeningResource($this->whenLoaded('subRekening')),
            'tahun_anggaran_id' => $this->tahun_anggaran_id,
            'nama_penerimaan' => $this->nama_penerimaan,
            'target' => (float) $this->target,
            // Computed from SUM(transaksi_penerimaans.realisasi) via the model accessor.
            'realisasi' => (float) $this->realisasi,
            'persentase' => (float) $this->persentase,
            'transaksi_count' => $this->whenCounted('transaksiPenerimaans'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
