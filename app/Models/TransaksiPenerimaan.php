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
        'penerimaan_id', 'nomor_registrasi', 'realisasi', 'tanggal', 'keterangan',
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
