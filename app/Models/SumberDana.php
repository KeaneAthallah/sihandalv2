<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SumberDana extends Model
{
    protected $fillable = ['opd_id', 'nama_sumber_dana', 'pagu', 'realisasi', 'dana_di_commit', 'persentase'];

    protected $casts = [
        'pagu' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'dana_di_commit' => 'decimal:2',
        'persentase' => 'decimal:2',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function availablePagu(): float
    {
        return (float) ($this->pagu - $this->realisasi - $this->dana_di_commit);
    }

    public function commit(float $amount): void
    {
        $this->dana_di_commit += $amount;
        $this->save();
    }

    public function releaseCommit(float $amount): void
    {
        $this->dana_di_commit = max(0, (float) $this->dana_di_commit - $amount);
        $this->save();
    }

    public function realize(float $amount): void
    {
        $this->realisasi += $amount;
        $this->dana_di_commit = max(0, (float) $this->dana_di_commit - $amount);
        $this->persentase = $this->pagu > 0 ? round(((float) $this->realisasi / (float) $this->pagu) * 100, 2) : 0;
        $this->save();
    }
}
