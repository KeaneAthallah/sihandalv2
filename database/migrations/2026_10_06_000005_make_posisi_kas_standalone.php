<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Posisi Kas is its own record for reporting: it names a rekening
     * directly (nama + nomor) and carries a single saldo, instead of
     * being tied to an accounting rekening (kas leaf) and derived from
     * saldo awal / penerimaan / pengeluaran.
     */
    public function up(): void
    {
        Schema::table('posisi_kas', function (Blueprint $table) {
            $table->string('nama_rekening', 255)->nullable()->after('opd_id');
            $table->string('nomor_rekening', 100)->nullable()->after('nama_rekening');
            $table->decimal('saldo', 18, 2)->default(0)->after('nomor_rekening');
        });

        Schema::table('posisi_kas', function (Blueprint $table) {
            $table->dropForeign(['rekening_id']);
            $table->dropColumn('rekening_id');
            $table->dropColumn(['saldo_awal', 'penerimaan', 'pengeluaran', 'saldo_akhir']);
        });
    }

    public function down(): void
    {
        Schema::table('posisi_kas', function (Blueprint $table) {
            $table->decimal('saldo_awal', 18, 2)->default(0);
            $table->decimal('penerimaan', 18, 2)->default(0);
            $table->decimal('pengeluaran', 18, 2)->default(0);
            $table->decimal('saldo_akhir', 18, 2)->default(0);
            $table->foreignId('rekening_id')->nullable()->constrained('rekenings')->cascadeOnDelete();

            $table->dropColumn(['nama_rekening', 'nomor_rekening', 'saldo']);
        });
    }
};
