<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Books a Rekening Bank on each BKU detail row of a Transaksi Penerimaan.
     *
     * One transaction may receive money across several physical bank accounts,
     * mirroring how each BKU row carries its own nomor, tanggal, nilai, and
     * accounting rekening. The bank is chosen inline on the BKU row through a
     * pop-up picker, so rows created before this migration carry none until
     * they are edited.
     */
    public function up(): void
    {
        Schema::table('transaksi_penerimaan_bkus', function (Blueprint $table) {
            $table->foreignId('rekening_bank_id')
                ->nullable()
                ->after('rekening_id')
                ->constrained('rekening_banks')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penerimaan_bkus', function (Blueprint $table) {
            $table->dropForeign(['rekening_bank_id']);
            $table->dropColumn('rekening_bank_id');
        });
    }
};
