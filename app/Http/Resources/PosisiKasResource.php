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
            'rekening_id' => $this->rekening_id,
            'rekening' => new RekeningResource($this->whenLoaded('rekening')),
            'tanggal' => $this->tanggal?->toDateString(),
            'saldo_awal' => (float) $this->saldo_awal,
            'penerimaan' => (float) $this->penerimaan,
            'pengeluaran' => (float) $this->pengeluaran,
            'saldo_akhir' => (float) $this->saldo_akhir,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
