<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferDana extends Model
{
    protected $fillable = [
        'nomor_transfer', 'opd_id', 'jumlah',
        'sumber_dana_pengirim_id', 'sumber_dana_penerima_id',
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

    /**
     * The sumber dana the funds move out of (e.g. DAK).
     */
    public function sumberDanaPengirim(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class, 'sumber_dana_pengirim_id');
    }

    /**
     * The sumber dana the funds move into (e.g. DAU).
     */
    public function sumberDanaPenerima(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class, 'sumber_dana_penerima_id');
    }
}
