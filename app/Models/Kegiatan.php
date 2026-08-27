<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kegiatan extends Model
{
    protected $table = 'kegiatan';

    protected $fillable = [
        'program_id', 'opd_id', 'sumber_dana_id',
        'kode_kegiatan', 'nama_kegiatan',
        'kode_sub_kegiatan', 'nama_sub_kegiatan',
        'kode_rekening', 'nama_rekening',
        'pagu', 'realisasi', 'persentase',
    ];

    protected $casts = [
        'pagu' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persentase' => 'decimal:2',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }
}
