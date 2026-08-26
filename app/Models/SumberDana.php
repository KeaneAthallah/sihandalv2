<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SumberDana extends Model
{
    use Auditable;

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
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Commit amount must be positive.');
        }

        $available = $this->availablePagu();
        if ($amount > $available) {
            throw new \RuntimeException("Insufficient available pagu. Available: {$available}, requested: {$amount}");
        }

        $this->dana_di_commit = (float) $this->dana_di_commit + $amount;
        $this->save();
    }

    public function releaseCommit(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Release amount must be positive.');
        }

        $this->dana_di_commit = max(0, (float) $this->dana_di_commit - $amount);
        $this->save();
    }

    public function realize(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Realize amount must be positive.');
        }

        $this->realisasi = (float) $this->realisasi + $amount;
        $this->dana_di_commit = max(0, (float) $this->dana_di_commit - $amount);
        $this->persentase = $this->pagu > 0 ? round(((float) $this->realisasi / (float) $this->pagu) * 100, 2) : 0;
        $this->save();
    }

    public function recalculatePersentase(): void
    {
        $this->persentase = (float) $this->pagu > 0
            ? round(((float) $this->realisasi / (float) $this->pagu) * 100, 2)
            : 0;
    }
}
