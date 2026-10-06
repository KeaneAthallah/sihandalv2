<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengeluaran mirrors Permintaan Dana: the amount becomes `jumlah`
     * (with `keperluan`) and gains the SP2D reference, replacing the old
     * anggaran/realisasi/persentase and denormalised kegiatan columns.
     */
    public function up(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->decimal('jumlah', 18, 2)->default(0)->after('sumber_dana_id');
            $table->string('keperluan', 255)->nullable()->after('jumlah');
            $table->string('no_sp2d', 100)->nullable()->after('keperluan');
            $table->date('tanggal_sp2d')->nullable()->after('no_sp2d');

            $table->dropColumn(['anggaran', 'realisasi', 'persentase']);
            $table->dropColumn(['kode_kegiatan', 'nama_kegiatan', 'keterangan']);
        });
    }

    public function down(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->decimal('anggaran', 18, 2)->default(0);
            $table->decimal('realisasi', 18, 2)->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->string('kode_kegiatan', 50)->nullable();
            $table->string('nama_kegiatan', 255)->nullable();
            $table->string('keterangan', 255)->nullable();

            $table->dropColumn(['jumlah', 'keperluan', 'no_sp2d', 'tanggal_sp2d']);
        });
    }
};
