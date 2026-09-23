<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Rekening Bank = a physical/operational bank account that receives and holds
 * government funds. This is NOT the accounting `Rekening` master (kas /
 * pendapatan / belanja) and the two concepts must never be conflated.
 *
 * Banks are a global master (shared by all OPDs) and are booked directly on
 * each Transaksi Penerimaan.
 */
class RekeningBank extends Model
{
    use Auditable;

    protected $fillable = ['bank_name', 'account_number', 'account_name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Human-friendly dropdown label: "Bank Name — Account Number — Account Name".
     */
    public function getLabelAttribute(): string
    {
        return trim("{$this->bank_name} — {$this->account_number} — {$this->account_name}");
    }
}
