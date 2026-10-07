<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransaksiPenerimaan extends Model
{
    use Auditable;

    protected $fillable = [
        'penerimaan_id',
        'sumber_dana_id',
        'nomor_registrasi', 'realisasi', 'tanggal', 'keterangan',
        'source_file', 'source_row', 'source_identifier',
    ];

    protected $casts = [
        'realisasi' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(Penerimaan::class);
    }

    /**
     * The sumber dana this cash receipt is attributed to, used
     * to compute the cash balance per fund source.
     */
    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function bkus(): HasMany
    {
        return $this->hasMany(TransaksiPenerimaanBku::class);
    }

    /**
     * Total value of all BKU details for this transaction.
     */
    public function totalBku(): float
    {
        if ($this->relationLoaded('bkus')) {
            return (float) $this->bkus->sum('nilai');
        }

        return (float) $this->bkus()->sum('nilai');
    }

    public function opdId(): ?int
    {
        return $this->penerimaan?->opd_id;
    }
}
