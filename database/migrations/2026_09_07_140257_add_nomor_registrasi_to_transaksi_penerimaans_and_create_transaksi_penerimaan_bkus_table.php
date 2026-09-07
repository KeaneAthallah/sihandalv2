<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the transaction registration number (nomor_registrasi) to
     * transaksi_penerimaans and creates the detail BKU table
     * (transaksi_penerimaan_bkus).
     *
     * nomor_registrasi is nullable so legacy imported rows (which have no
     * registration number) keep working; new manual transactions require it.
     */
    public function up(): void
    {
        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            $table->string('nomor_registrasi', 100)->nullable()->after('penerimaan_id');
        });

        Schema::create('transaksi_penerimaan_bkus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_penerimaan_id')
                ->constrained('transaksi_penerimaans')
                ->cascadeOnDelete();
            $table->string('nomor_bku', 100);
            $table->date('tanggal_bku')->nullable();
            $table->decimal('nilai', 18, 2)->default(0);
            $table->foreignId('rekening_id')
                ->nullable()
                ->constrained('rekenings')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_penerimaan_bkus');

        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            $table->dropColumn('nomor_registrasi');
        });
    }
};
