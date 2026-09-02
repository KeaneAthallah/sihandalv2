<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Belanja extends Model
{
    use Auditable;

    protected $fillable = [
        'sub_kegiatan_id', 'rekening_id', 'sumber_dana_id', 'opd_id',
        'pagu', 'realisasi', 'dana_di_commit', 'tahun_anggaran_id',
        'source_file', 'source_row', 'source_identifier',
    ];

    protected $casts = [
        'pagu' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'dana_di_commit' => 'decimal:2',
    ];

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class);
    }

    public function rekening(): BelongsTo
    {
        return $this->belongsTo(Rekening::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class);
    }

    public function availablePagu(): float
    {
        return (float) ($this->pagu - $this->dana_di_commit - $this->realisasi);
    }

    public function commit(float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        if ($this->availablePagu() < $amount) {
            throw new \RuntimeException('Dana commit melebihi pagu yang tersedia.');
        }

        $this->increment('dana_di_commit', $amount);
    }

    public function releaseCommit(float $amount): void
    {
        $this->decrement('dana_di_commit', max(0, $amount));
    }

    public function realize(float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        if ($this->availablePagu() < $amount) {
            throw new \RuntimeException('Realisasi melebihi pagu yang tersedia.');
        }

        $this->increment('realisasi', $amount);
        $this->decrement('dana_di_commit', min($amount, (float) $this->dana_di_commit));
    }
}
