<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    protected $fillable = ['kode', 'nama', 'kode_sub_unit', 'nama_sub_unit', 'total_pagu'];

    protected $casts = [
        'total_pagu' => 'decimal:2',
    ];

    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }

    public function penerimaans(): HasMany
    {
        return $this->hasMany(Penerimaan::class);
    }

    public function pengeluarans(): HasMany
    {
        return $this->hasMany(Pengeluaran::class);
    }

    public function permintaanDanas(): HasMany
    {
        return $this->hasMany(PermintaanDana::class);
    }

    public function transferDanas(): HasMany
    {
        return $this->hasMany(TransferDana::class);
    }

    public function posisiKas(): HasMany
    {
        return $this->hasMany(PosisiKas::class);
    }
}
