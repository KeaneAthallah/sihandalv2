<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    protected $fillable = [
        'opd_id', 'rekening_id', 'kode_kegiatan', 'nama_kegiatan',
        'sumber_dana', 'anggaran', 'realisasi', 'persentase',
        'tanggal', 'keterangan',
    ];

    protected $casts = [
        'anggaran' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persentase' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }
}
