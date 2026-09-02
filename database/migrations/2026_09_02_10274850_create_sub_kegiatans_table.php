<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->string('kode_sub_kegiatan', 100);
            $table->text('nama_sub_kegiatan');
            $table->decimal('pagu', 18, 2)->default(0);
            $table->decimal('realisasi', 18, 2)->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->timestamps();

            $table->index(['kegiatan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_kegiatans');
    }
};
