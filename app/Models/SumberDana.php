<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SumberDana extends Model
{
    use Auditable;

    protected $fillable = ['nama_sumber_dana', 'source_file', 'source_identifier'];

    public function belanjas(): HasMany
    {
        return $this->hasMany(Belanja::class);
    }

    public function pengeluarans(): HasMany
    {
        return $this->hasMany(Pengeluaran::class);
    }

    public function permintaanDanas(): HasMany
    {
        return $this->hasMany(PermintaanDana::class);
    }

    /**
     * Cash receipts attributed to this fund source; the basis of
     * the per-sumber-dana cash balance.
     */
    public function transaksiPenerimaans(): HasMany
    {
        return $this->hasMany(TransaksiPenerimaan::class);
    }

    /**
     * Cash reservation rows (permintaan dana commit) for this fund source.
     */
    public function kasSumberDanas(): HasMany
    {
        return $this->hasMany(KasSumberDana::class);
    }
}
