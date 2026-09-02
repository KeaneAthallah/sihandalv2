<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubKegiatan extends Model
{
    use Auditable;

    protected $fillable = [
        'kegiatan_id', 'kode_sub_kegiatan', 'nama_sub_kegiatan',
        'pagu', 'realisasi', 'persentase',
        'source_file', 'source_identifier',
    ];

    protected $casts = [
        'pagu' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persentase' => 'decimal:2',
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }

    public function belanjas(): HasMany
    {
        return $this->hasMany(Belanja::class);
    }

    public function paguBelanja()
    {
        return $this->belanjas()->sum('pagu');
    }

    public function realisasiBelanja()
    {
        return $this->belanjas()->sum('realisasi');
    }

    public function opdId(): ?int
    {
        return $this->kegiatan?->opd_id;
    }
}
