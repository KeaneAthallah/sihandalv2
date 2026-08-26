<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Persetujuan extends Model
{
    protected $fillable = ['permintaan_dana_id', 'user_id', 'keputusan', 'catatan'];

    public function permintaanDana(): BelongsTo
    {
        return $this->belongsTo(PermintaanDana::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
