<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferDana extends Model
{
    protected $fillable = [
        'nomor_transfer', 'opd_id', 'jumlah', 'sumber_dana',
        'keterangan', 'status', 'tanggal', 'tanggal_selesai',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }
}
