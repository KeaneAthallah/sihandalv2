<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posisi_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rekening_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal')->nullable();
            $table->decimal('saldo_awal', 18, 2)->default(0);
            $table->decimal('penerimaan', 18, 2)->default(0);
            $table->decimal('pengeluaran', 18, 2)->default(0);
            $table->decimal('saldo_akhir', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posisi_kas');
    }
};
