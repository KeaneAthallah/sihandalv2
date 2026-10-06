<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPenerimaanBku extends Model
{
    use Auditable;

    protected $fillable = [
        'transaksi_penerimaan_id', 'opd_id', 'nomor_bku', 'tanggal_bku', 'nilai',
        'rekening_id', 'sub_rekening_id', 'rekening_bank_id',
    ];

    protected $casts = [
        'tanggal_bku' => 'date',
        'nilai' => 'decimal:2',
    ];

    public function transaksiPenerimaan(): BelongsTo
    {
        return $this->belongsTo(TransaksiPenerimaan::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * The accounting rekening (utama) this BKU row is booked against.
     */
    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'rekening_id');
    }

    /**
     * The sub rekening (detail) of rekening(), if any.
     */
    public function subRekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class, 'sub_rekening_id');
    }

    /**
     * The Rekening Bank this BKU row is booked into. A transaction may span
     * several physical bank accounts, one per BKU row.
     */
    public function rekeningBank(): BelongsTo
    {
        return $this->belongsTo(RekeningBank::class, 'rekening_bank_id');
    }

    /**
     * The OPD owner of this BKU detail, resolved through the parent chain
     * (BKU -> TransaksiPenerimaan -> Penerimaan -> Opd).
     */
    public function opdId(): ?int
    {
        return $this->opd_id ?? $this->transaksiPenerimaan?->opdId();
    }
}
