<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    protected $fillable = [
        'opd_id', 'kode_kegiatan', 'nama_kegiatan',
        'kode_sub_kegiatan', 'nama_sub_kegiatan',
        'kode_rekening', 'nama_rekening',
        'sumber_dana', 'pagu', 'realisasi', 'persentase',
    ];

    protected $casts = [
        'pagu' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persentase' => 'decimal:2',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }
}
