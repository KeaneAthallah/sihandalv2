<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sumber_danas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained()->cascadeOnDelete();
            $table->string('nama_sumber_dana');
            $table->decimal('pagu', 18, 2)->default(0);
            $table->decimal('realisasi', 18, 2)->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sumber_danas');
    }
};
