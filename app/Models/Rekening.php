<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    protected $fillable = ['kode', 'nama', 'tipe', 'saldo'];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];
}
