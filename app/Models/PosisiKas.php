<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosisiKas extends Model
{
    protected $fillable = [
        'opd_id', 'tanggal', 'nama_rekening', 'nomor_rekening', 'saldo',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'saldo' => 'decimal:2',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }
}
