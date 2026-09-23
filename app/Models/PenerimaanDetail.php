<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PenerimaanDetail groups a Sumber Dana under a Penerimaan master.
 * Realization is never stored here: a master's realization remains the SUM of
 * its transactions, and transactions may optionally point back to a detail
 * through transaksi_penerimaans.penerimaan_detail_id.
 */
class PenerimaanDetail extends Model
{
    use Auditable;

    protected $fillable = ['penerimaan_id', 'sumber_dana_id'];

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(Penerimaan::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }
}
