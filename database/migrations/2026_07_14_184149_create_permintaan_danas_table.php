<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_danas', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_permintaan')->unique();
            $table->foreignId('opd_id')->constrained()->cascadeOnDelete();
            $table->decimal('jumlah', 18, 2)->default(0);
            $table->string('sumber_dana');
            $table->string('keperluan');
            $table->enum('status', ['draft', 'menunggu', 'disetujui', 'ditolak'])->default('draft');
            $table->date('tanggal')->nullable();
            $table->date('tanggal_disetujui')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_danas');
    }
};
