<?php

namespace App\Http\Resources;

use App\Models\Rekening;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Rekening
 */
class RekeningResource extends JsonResource
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
            'kode' => $this->kode,
            'nama' => $this->nama,
            'tipe' => $this->tipe,
            'parent_id' => $this->parent_id,
            'parent' => new RekeningResource($this->whenLoaded('parent')),
            'children_count' => $this->whenCounted('children'),
            'is_kas_leaf' => $this->when(
                $this->resource instanceof Rekening,
                fn () => $this->tipe === 'kas' && $this->children_count === 0,
            ),
        ];
    }
}
