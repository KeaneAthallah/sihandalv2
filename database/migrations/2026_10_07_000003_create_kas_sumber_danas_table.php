<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reservasi kas per (OPD, sumber dana), meniru dana_di_commit
     * pada belanja. Baris ini dikunci lockForUpdate saat permintaan
     * dana submit/approve/reject supaya dua permintaan tidak bisa
     * menembus kas yang sama.
     */
    public function up(): void
    {
        Schema::create('kas_sumber_danas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->foreignId('sumber_dana_id')->constrained('sumber_danas')->cascadeOnDelete();
            $table->decimal('di_commit', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['opd_id', 'sumber_dana_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas_sumber_danas');
    }
};
