<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Each BKU detail row books into an OPD and an accounting rekening
     * (rekening utama + optional sub rekening), alongside its existing
     * nomor, tanggal, nilai and physical rekening bank.
     */
    public function up(): void
    {
        Schema::table('transaksi_penerimaan_bkus', function (Blueprint $table) {
            $table->foreignId('opd_id')
                ->nullable()
                ->after('transaksi_penerimaan_id')
                ->constrained('opds')
                ->nullOnDelete();

            $table->foreignId('rekening_id')
                ->nullable()
                ->after('opd_id')
                ->constrained('rekenings')
                ->nullOnDelete();

            $table->foreignId('sub_rekening_id')
                ->nullable()
                ->after('rekening_id')
                ->constrained('rekenings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penerimaan_bkus', function (Blueprint $table) {
            $table->dropForeign(['sub_rekening_id']);
            $table->dropForeign(['rekening_id']);
            $table->dropForeign(['opd_id']);
            $table->dropColumn(['opd_id', 'rekening_id', 'sub_rekening_id']);
        });
    }
};
