<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    protected $fillable = ['kode', 'nama', 'nmskpd', 'kode_sub_unit', 'nama_sub_unit', 'total_pagu', 'dinas_id', 'unit_id', 'source_file', 'source_identifier'];

    protected $casts = [
        'total_pagu' => 'decimal:2',
    ];

    public function dinas(): BelongsTo
    {
        return $this->belongsTo(Dinas::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function dinasList(): HasMany
    {
        return $this->hasMany(Dinas::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function upts(): HasMany
    {
        return $this->hasMany(Upt::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function kegiatans(): HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }

    public function belanjas(): HasMany
    {
        return $this->hasMany(Belanja::class);
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

    public function paguProgram()
    {
        return $this->programs()->withSum('kegiatans as total_pagu', 'pagu')->get()->sum('total_pagu');
    }

    public function paguBelanja()
    {
        return $this->belanjas()->sum('pagu');
    }
}
