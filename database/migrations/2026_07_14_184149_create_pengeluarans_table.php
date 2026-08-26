<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rekening_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kode_kegiatan')->nullable();
            $table->string('nama_kegiatan')->nullable();
            $table->string('sumber_dana');
            $table->decimal('anggaran', 18, 2)->default(0);
            $table->decimal('realisasi', 18, 2)->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->date('tanggal')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluarans');
    }
};
