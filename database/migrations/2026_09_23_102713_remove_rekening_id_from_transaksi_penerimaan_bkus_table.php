<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the accounting rekening_id from BKU rows. The physical bank
     * account (rekening_bank_id) already identifies where each BKU was
     * booked, so the accounting rekening is no longer tracked per row.
     */
    public function up(): void
    {
        Schema::table('transaksi_penerimaan_bkus', function (Blueprint $table) {
            $table->dropForeign(['rekening_id']);
            $table->dropColumn('rekening_id');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penerimaan_bkus', function (Blueprint $table) {
            $table->foreignId('rekening_id')
                ->nullable()
                ->after('transaksi_penerimaan_id')
                ->constrained('rekenings')
                ->nullOnDelete();
        });
    }
};
