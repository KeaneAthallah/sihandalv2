<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPenerimaan extends Model
{
    use Auditable;

    protected $fillable = [
        'penerimaan_id', 'realisasi', 'tanggal', 'keterangan',
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

    public function opdId(): ?int
    {
        return $this->penerimaan?->opd_id;
    }
}
