<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill normalized sub_kegiatans/belanjas from legacy kegiatan
     * text columns (kode/nama_sub_kegiatan, kode/nama_rekening).
     *
     * Widens the target text columns first, then copies legacy data.
     * Idempotent: skips kegiatan that already have a sub_kegiatan.
     */
    public function up(): void
    {
        if (! Schema::hasTable('kegiatan') || ! Schema::hasTable('sub_kegiatans')) {
            return;
        }

        Schema::table('sub_kegiatans', function ($table) {
            $table->text('kode_sub_kegiatan')->change();
            $table->text('nama_sub_kegiatan')->change();
        });

        $kegiatans = DB::table('kegiatan')
            ->leftJoin('sub_kegiatans', 'sub_kegiatans.kegiatan_id', '=', 'kegiatan.id')
            ->whereNull('sub_kegiatans.id')
            ->whereNotNull('kegiatan.kode_sub_kegiatan')
            ->select('kegiatan.*')
            ->get();

        foreach ($kegiatans as $kegiatan) {
            $subId = DB::table('sub_kegiatans')->insertGetId([
                'kegiatan_id' => $kegiatan->id,
                'kode_sub_kegiatan' => $kegiatan->kode_sub_kegiatan ?: null,
                'nama_sub_kegiatan' => $kegiatan->nama_sub_kegiatan ?: null,
                'pagu' => $kegiatan->pagu ?? 0,
                'realisasi' => $kegiatan->realisasi ?? 0,
                'persentase' => $kegiatan->persentase ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! Schema::hasTable('belanjas')) {
                continue;
            }

            $rekeningId = null;
            if (! empty($kegiatan->kode_rekening)) {
                $rekening = DB::table('rekenings')->where('kode', $kegiatan->kode_rekening)->first();
                if ($rekening) {
                    $rekeningId = $rekening->id;
                } else {
                    $rekeningId = DB::table('rekenings')->insertGetId([
                        'kode' => $kegiatan->kode_rekening,
                        'nama' => $kegiatan->nama_rekening ?: $kegiatan->kode_rekening,
                        'tipe' => 'belanja',
                        'saldo' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $exists = DB::table('belanjas')->where('sub_kegiatan_id', $subId)->exists();
            if (! $exists) {
                DB::table('belanjas')->insert([
                    'sub_kegiatan_id' => $subId,
                    'rekening_id' => $rekeningId,
                    'sumber_dana_id' => $kegiatan->sumber_dana_id ?: null,
                    'opd_id' => $kegiatan->opd_id,
                    'pagu' => $kegiatan->pagu ?? 0,
                    'realisasi' => $kegiatan->realisasi ?? 0,
                    'dana_di_commit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Data backfill; no structural reverse needed.
    }
};
