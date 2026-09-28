<?php

namespace App\Http\Resources;

use App\Models\PenerimaanDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PenerimaanDetail
 */
class PenerimaanDetailResource extends JsonResource
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
            'sumber_dana_id' => $this->sumber_dana_id,
            'sumber_dana' => new SumberDanaResource($this->whenLoaded('sumberDana')),
            'penerimaan' => new PenerimaanResource($this->whenLoaded('penerimaan')),
        ];
    }
}
