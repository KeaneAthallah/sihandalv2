<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class KasSumberDana extends Model
{
    protected $table = 'kas_sumber_danas';

    protected $fillable = ['opd_id', 'sumber_dana_id', 'di_commit'];

    protected $casts = [
        'di_commit' => 'decimal:2',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function sumberDana(): BelongsTo
    {
        return $this->belongsTo(SumberDana::class);
    }

    /**
     * Resolve (or create) the reservation row for an OPD + sumber
     * dana pair so it can be locked and adjusted atomically. The
     * query-exception fallback absorbs a concurrent insert racing
     * on the unique (opd_id, sumber_dana_id) constraint.
     */
    public static function forPair(int $opdId, int $sumberDanaId): self
    {
        $row = static::query()
            ->where('opd_id', $opdId)
            ->where('sumber_dana_id', $sumberDanaId)
            ->first();

        if ($row !== null) {
            return $row;
        }

        try {
            return static::query()->create([
                'opd_id' => $opdId,
                'sumber_dana_id' => $sumberDanaId,
                'di_commit' => 0,
            ]);
        } catch (QueryException) {
            return static::query()
                ->where('opd_id', $opdId)
                ->where('sumber_dana_id', $sumberDanaId)
                ->firstOrFail();
        }
    }

    /**
     * Reserve cash against this OPD + sumber dana pair.
     */
    public function commit(float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($amount) {
            $row = static::query()
                ->where('opd_id', $this->opd_id)
                ->where('sumber_dana_id', $this->sumber_dana_id)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                return;
            }

            $row->update([
                'di_commit' => round((float) $row->di_commit + $amount, 2),
            ]);

            $this->refresh();
        });
    }

    /**
     * Release a previously reserved amount, capped at what is
     * actually committed so it can never go negative.
     */
    public function release(float $amount): void
    {
        DB::transaction(function () use ($amount) {
            $row = static::query()
                ->where('opd_id', $this->opd_id)
                ->where('sumber_dana_id', $this->sumber_dana_id)
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                return;
            }

            $released = min(max($amount, 0), (float) $row->di_commit);

            if ($released <= 0) {
                return;
            }

            $row->update([
                'di_commit' => round((float) $row->di_commit - $released, 2),
            ]);

            $this->refresh();
        });
    }
}
