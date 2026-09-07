<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPenerimaanBku extends Model
{
    use Auditable;

    protected $fillable = [
        'transaksi_penerimaan_id', 'nomor_bku', 'tanggal_bku', 'nilai', 'rekening_id',
    ];

    protected $casts = [
        'tanggal_bku' => 'date',
        'nilai' => 'decimal:2',
    ];

    public function transaksiPenerimaan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenerimaan::class);
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    /**
     * The OPD owner of this BKU detail, resolved through the parent chain
     * (BKU -> TransaksiPenerimaan -> Penerimaan -> Opd).
     */
    public function opdId(): ?int
    {
        return $this->transaksiPenerimaan?->opdId();
    }
}
