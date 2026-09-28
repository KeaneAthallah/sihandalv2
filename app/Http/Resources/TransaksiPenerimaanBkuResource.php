<?php

namespace App\Http\Resources;

use App\Models\TransaksiPenerimaanBku;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TransaksiPenerimaanBku
 */
class TransaksiPenerimaanBkuResource extends JsonResource
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
            'transaksi_penerimaan_id' => $this->transaksi_penerimaan_id,
            'nomor_bku' => $this->nomor_bku,
            'tanggal_bku' => $this->tanggal_bku?->toDateString(),
            'nilai' => (float) $this->nilai,
            'rekening_bank_id' => $this->rekening_bank_id,
            'rekening_bank' => new RekeningBankResource($this->whenLoaded('rekeningBank')),
        ];
    }
}
