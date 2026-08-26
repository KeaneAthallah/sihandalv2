<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_danas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_transfer')->unique();
            $table->foreignId('opd_id')->constrained()->cascadeOnDelete();
            $table->decimal('jumlah', 18, 2)->default(0);
            $table->string('sumber_dana');
            $table->string('keterangan')->nullable();
            $table->enum('status', ['draft', 'diproses', 'selesai', 'gagal'])->default('draft');
            $table->date('tanggal')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_danas');
    }
};
