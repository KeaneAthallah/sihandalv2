<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengeluaran bisa dibuat dari permintaan dana yang disetujui.
     * Unique agar satu permintaan dana maksimal menghasilkan satu pengeluaran.
     */
    public function up(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->foreignId('permintaan_dana_id')
                ->nullable()
                ->after('tahun_anggaran_id')
                ->constrained('permintaan_danas')
                ->nullOnDelete()
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropUnique(['permintaan_dana_id']);
            $table->dropForeign(['permintaan_dana_id']);
            $table->dropColumn('permintaan_dana_id');
        });
    }
};
