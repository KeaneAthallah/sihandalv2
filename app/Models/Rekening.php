<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

class Rekening extends Model
{
    protected $fillable = ['kode', 'nama', 'tipe', 'source_file', 'source_identifier'];

    public function belanjas(): HasMany
    {
        return $this->hasMany(Belanja::class);
    }

    public function penerimaans(): HasMany
    {
        return $this->hasMany(Penerimaan::class);
    }

    public function pengeluarans(): HasMany
    {
        return $this->hasMany(Pengeluaran::class);
    }

    public function transaksiPenerimaans(): HasManyThrough
    {
        return $this->hasManyThrough(
            TransaksiPenerimaan::class,
            Penerimaan::class,
            'rekening_id',
            'penerimaan_id',
            'id',
            'id'
        );
    }

    /**
     * Total realized penerimaan for this rekening, optionally scoped to one OPD.
     * Aggregated in the database, never loaded into PHP.
     */
    public function totalPenerimaan(?int $opdId = null): float
    {
        return (float) DB::table('transaksi_penerimaans as t')
            ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
            ->where('p.rekening_id', $this->id)
            ->when($opdId, fn ($q) => $q->where('p.opd_id', $opdId))
            ->sum('t.realisasi');
    }

    /**
     * Total realized pengeluaran for this rekening, optionally scoped to one OPD.
     */
    public function totalPengeluaran(?int $opdId = null): float
    {
        return (float) DB::table('pengeluarans')
            ->where('rekening_id', $this->id)
            ->when($opdId, fn ($q) => $q->where('opd_id', $opdId))
            ->sum('realisasi');
    }

    /**
     * Calculated cash balance = total penerimaan - total pengeluaran.
     * Never persisted; always derived from the financial transactions.
     */
    public function saldo(?int $opdId = null): float
    {
        return round($this->totalPenerimaan($opdId) - $this->totalPengeluaran($opdId), 2);
    }
}
