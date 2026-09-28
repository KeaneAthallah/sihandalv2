<?php

namespace App\Http\Resources;

use App\Models\RekeningBank;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RekeningBank
 */
class RekeningBankResource extends JsonResource
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
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'is_active' => $this->is_active,
            'label' => $this->when($this->resource instanceof RekeningBank, fn () => $this->label),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
