<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('belanjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_kegiatan_id')->constrained('sub_kegiatans')->cascadeOnDelete();
            $table->foreignId('rekening_id')->constrained('rekenings')->cascadeOnDelete();
            $table->foreignId('sumber_dana_id')->nullable()->constrained('sumber_danas')->nullOnDelete();
            $table->foreignId('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->decimal('pagu', 18, 2)->default(0);
            $table->decimal('realisasi', 18, 2)->default(0);
            $table->decimal('dana_di_commit', 18, 2)->default(0);
            $table->foreignId('tahun_anggaran_id')->nullable()->constrained('tahun_anggarans')->nullOnDelete();
            $table->timestamps();

            $table->index(['sub_kegiatan_id']);
            $table->index(['rekening_id']);
            $table->index(['sumber_dana_id']);
            $table->index(['opd_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('belanjas');
    }
};
