<?php

namespace App\Http\Resources;

use App\Models\DocumentCounter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Document counter resource (read-only, admin diagnostics).
 *
 * @mixin DocumentCounter
 */
class DocumentCounterResource extends JsonResource
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
            'type' => $this->type,
            'year' => (int) $this->year,
            'last_value' => (int) $this->last_value,
        ];
    }
}
