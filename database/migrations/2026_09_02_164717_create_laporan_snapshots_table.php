<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot table for BPKAD aggregate PDF reports (LAPORAN REALISASI
     * PENDAPATAN and POSISI KAS DAERAH).
     *
     * These are province-wide report *snapshots*, not individual financial
     * transactions. They are deliberately kept out of penerimaans /
     * transaksi_penerimaans / pengeluarans / posisi_kas so importing a PDF
     * never duplicates or alters the transactional data that the derived kas
     * balances rely on. Rows are immutable, read-only report lines.
     *
     * The many nullable decimal columns cover the three report shapes without
     * normalizing the source: revenue lines use target/realisasi_*; bank saldo
     * and selisih lines use nilai; sumber-dana lines use penerimaan/pengeluaran/sisa.
     */
    public function up(): void
    {
        Schema::create('laporan_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('jenis')->comment('realisasi_pendapatan | posisi_kas');
            $table->string('periode')->nullable();
            $table->unsignedInteger('tahun_anggaran')->nullable();
            $table->date('tanggal_laporan')->nullable();
            $table->string('signed_by')->nullable();
            $table->string('section')->nullable();
            $table->string('sub')->nullable();
            $table->string('tipe_baris')->default('rincian')->comment('rincian | group | total');
            $table->string('kode')->nullable();
            $table->unsignedTinyInteger('level')->nullable();
            $table->string('uraian')->nullable();
            $table->decimal('target', 18, 2)->nullable();
            $table->decimal('realisasi_bulan_ini', 18, 2)->nullable();
            $table->decimal('realisasi_sd_bulan_lalu', 18, 2)->nullable();
            $table->decimal('realisasi_sd_bulan_ini', 18, 2)->nullable();
            $table->decimal('persentase', 18, 2)->nullable();
            $table->decimal('lebih_kurang', 18, 2)->nullable();
            $table->decimal('nilai', 18, 2)->nullable();
            $table->decimal('penerimaan', 18, 2)->nullable();
            $table->decimal('pengeluaran', 18, 2)->nullable();
            $table->decimal('sisa', 18, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->string('source_file')->nullable();
            $table->unsignedInteger('source_row')->nullable();
            $table->string('source_identifier', 255)->nullable();
            $table->timestamps();

            $table->index(['jenis', 'periode']);
            $table->unique(['source_file', 'source_row']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_snapshots');
    }
};
