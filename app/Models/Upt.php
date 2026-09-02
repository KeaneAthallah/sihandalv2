<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upt extends Model
{
    protected $fillable = ['kode', 'nama', 'opd_id'];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }
}
