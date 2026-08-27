<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opd_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sumber_dana_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kode_kegiatan', 50);
            $table->text('nama_kegiatan');
            $table->string('kode_sub_kegiatan', 50)->nullable();
            $table->text('nama_sub_kegiatan')->nullable();
            $table->string('kode_rekening', 50)->nullable();
            $table->text('nama_rekening')->nullable();
            $table->decimal('pagu', 18, 2)->default(0);
            $table->decimal('realisasi', 18, 2)->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->timestamps();

            $table->index(['opd_id', 'program_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
