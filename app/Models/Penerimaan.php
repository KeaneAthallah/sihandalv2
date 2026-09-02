<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Penerimaan extends Model
{
    use Auditable;

    protected $fillable = [
        'opd_id', 'rekening_id', 'sumber_dana_id', 'tahun_anggaran_id',
        'kode_sumber_dana', 'nama_sumber_dana',
        'target',
        'source_file', 'source_identifier',
    ];

    protected $casts = [
        'target' => 'decimal:2',
    ];

    /**
     * The master's total realization, derived from its transactions. This is a
     * computed value (SUM of transaksi_penerimaans.realisasi), never stored.
     */
    public function getRealisasiAttribute(): float
    {
        if ($this->relationLoaded('transaksiPenerimaans')) {
            return (float) $this->transaksiPenerimaans->sum('realisasi');
        }

        return (float) $this->transaksiPenerimaans()->sum('realisasi');
    }

    /**
     * Realization percentage of target. Handles target = 0 and no transactions.
     */
    public function getPersentaseAttribute(): float
    {
        $target = (float) $this->target;
        if ($target <= 0) {
            return 0;
        }

        return round($this->realisasi / $target * 100, 2);
    }

    /**
     * Latest transaction date, so views that previously rendered a single
     * tanggal on the master keep working after the split.
     */
    public function getTanggalAttribute(): ?Carbon
    {
        if ($this->relationLoaded('transaksiPenerimaans')) {
            $date = $this->transaksiPenerimaans->max('tanggal');
        } else {
            $date = $this->transaksiPenerimaans()->max('tanggal');
        }

        return $date ? Carbon::parse($date) : null;
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function transaksiPenerimaans(): HasMany
    {
        return $this->hasMany(TransaksiPenerimaan::class);
    }
}
