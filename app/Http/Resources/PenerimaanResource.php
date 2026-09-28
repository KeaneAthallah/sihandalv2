<?php

namespace App\Http\Resources;

use App\Models\Penerimaan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Penerimaan master resource. Realization is never persisted on the master:
 * target/realisasi/persentase are computed here from transaction aggregation.
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
            'sumber_dana_id' => $this->sumber_dana_id,
            'sumber_dana' => new SumberDanaResource($this->whenLoaded('sumberDana')),
            'tahun_anggaran_id' => $this->tahun_anggaran_id,
            'kode_sumber_dana' => $this->kode_sumber_dana,
            'nama_sumber_dana' => $this->nama_sumber_dana,
            'target' => (float) $this->target,
            // Computed from SUM(transaksi_penerimaans.realisasi) via the model accessor.
            'realisasi' => (float) $this->realisasi,
            'persentase' => (float) $this->persentase,
            'details_count' => $this->whenCounted('details'),
            'transaksi_count' => $this->whenCounted('transaksiPenerimaans'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
