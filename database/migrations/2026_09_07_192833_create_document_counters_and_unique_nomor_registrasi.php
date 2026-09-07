<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds per-(type, year) counters used to auto-generate unique document
     * numbers, and enforces uniqueness of transaksi penerimaan registration
     * numbers at the database level (legacy imported rows stay nullable).
     */
    public function up(): void
    {
        Schema::create('document_counters', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('last_value')->default(0);
            $table->timestamps();

            $table->unique(['type', 'year']);
        });

        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            $table->unique('nomor_registrasi');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penerimaans', function (Blueprint $table) {
            $table->dropUnique('transaksi_penerimaans_nomor_registrasi_unique');
        });

        Schema::dropIfExists('document_counters');
    }
};
