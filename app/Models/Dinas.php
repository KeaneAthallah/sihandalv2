<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dinas extends Model
{
    protected $fillable = ['kode', 'nama', 'opd_id'];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function scopeForOpd(Builder $query, ?Opd $opd, ?User $user): Builder
    {
        if ($user !== null && ! $user->isAdmin() && $opd !== null) {
            return $query->where('opd_id', $opd->id);
        }

        return $query;
    }
}
