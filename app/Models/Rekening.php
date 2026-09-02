<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rekening extends Model
{
    protected $fillable = ['kode', 'nama', 'tipe', 'saldo', 'source_file', 'source_identifier'];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    public function belanjas(): HasMany
    {
        return $this->hasMany(Belanja::class);
    }
}
