<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'kode_program', 'nama_program',
    ];

    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }
}
