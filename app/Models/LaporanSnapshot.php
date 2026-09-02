<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Immutable snapshot line from a BPKAD PDF report (LAPORAN REALISASI
 * PENDAPATAN DAERAH or POSISI KAS DAERAH). Read-only aggregate data held
 * entirely outside the transactional tables; never feeds derivers.
 */
class LaporanSnapshot extends Model
{
    protected $fillable = [
        'jenis',
        'periode',
        'tahun_anggaran',
        'tanggal_laporan',
        'signed_by',
        'section',
        'sub',
        'tipe_baris',
        'kode',
        'level',
        'uraian',
        'target',
        'realisasi_bulan_ini',
        'realisasi_sd_bulan_lalu',
        'realisasi_sd_bulan_ini',
        'persentase',
        'lebih_kurang',
        'nilai',
        'penerimaan',
        'pengeluaran',
        'sisa',
        'keterangan',
        'source_file',
        'source_row',
        'source_identifier',
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'target' => 'decimal:2',
        'realisasi_bulan_ini' => 'decimal:2',
        'realisasi_sd_bulan_lalu' => 'decimal:2',
        'realisasi_sd_bulan_ini' => 'decimal:2',
        'persentase' => 'decimal:2',
        'lebih_kurang' => 'decimal:2',
        'nilai' => 'decimal:2',
        'penerimaan' => 'decimal:2',
        'pengeluaran' => 'decimal:2',
        'sisa' => 'decimal:2',
    ];

    public function scopeOfJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeOfPeriode(Builder $query, string $periode): Builder
    {
        return $query->where('periode', $periode);
    }
}
