<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosisiKas extends Model
{
    protected $fillable = [
        'opd_id', 'rekening_id', 'tanggal',
        'saldo_awal', 'penerimaan', 'pengeluaran', 'saldo_akhir',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'saldo_awal' => 'decimal:2',
        'penerimaan' => 'decimal:2',
        'pengeluaran' => 'decimal:2',
        'saldo_akhir' => 'decimal:2',
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
