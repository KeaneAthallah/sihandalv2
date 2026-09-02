<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unit extends Model
{
    protected $fillable = ['kode', 'nama', 'opd_id', 'dinas_id'];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function dinas(): BelongsTo
    {
        return $this->belongsTo(Dinas::class);
    }
}
