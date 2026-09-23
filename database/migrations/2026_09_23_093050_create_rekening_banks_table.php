<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the global Rekening Bank master.
     *
     * A Rekening Bank is an actual physical bank account that receives
     * government money. It is deliberately separate from the accounting
     * `rekenings` master (kas/pendapatan/belanja): the two must never be
     * conflated. The master is NOT scoped to an OPD — banks are shared across
     * all OPDs and are booked directly on each Transaksi Penerimaan.
     *
     * Each account number is unique system-wide.
     */
    public function up(): void
    {
        Schema::create('rekening_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('account_number')->unique();
            $table->string('account_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekening_banks');
    }
};
