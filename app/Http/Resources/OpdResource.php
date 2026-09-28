<?php

namespace App\Http\Resources;

use App\Models\Opd;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Opd
 */
class OpdResource extends JsonResource
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
            'nmskpd' => $this->nmskpd,
            'kode_sub_unit' => $this->kode_sub_unit,
            'nama_sub_unit' => $this->nama_sub_unit,
            'total_pagu' => $this->total_pagu !== null ? (float) $this->total_pagu : null,
            'dinas_id' => $this->dinas_id,
            'unit_id' => $this->unit_id,
            'dinas' => new DinasResource($this->whenLoaded('dinas')),
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'programs_count' => $this->whenCounted('programs'),
            'kegiatans_count' => $this->whenCounted('kegiatans'),
            'penerimaans_count' => $this->whenCounted('penerimaans'),
            'pengeluarans_count' => $this->whenCounted('pengeluarans'),
            'upts_count' => $this->whenCounted('upts'),
        ];
    }
}
