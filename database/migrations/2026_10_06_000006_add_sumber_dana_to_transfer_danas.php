<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A transfer moves funds from one sumber dana to another (e.g. DAK ->
     * DAU, or the reverse), so it carries two sumber dana references and
     * no free-text label.
     */
    public function up(): void
    {
        Schema::table('transfer_danas', function (Blueprint $table) {
            $table->foreignId('sumber_dana_pengirim_id')
                ->nullable()
                ->after('opd_id')
                ->constrained('sumber_danas')
                ->nullOnDelete();

            $table->foreignId('sumber_dana_penerima_id')
                ->nullable()
                ->after('sumber_dana_pengirim_id')
                ->constrained('sumber_danas')
                ->nullOnDelete();

            $table->dropColumn('sumber_dana');
        });
    }

    public function down(): void
    {
        Schema::table('transfer_danas', function (Blueprint $table) {
            $table->string('sumber_dana', 255)->nullable();
            $table->dropForeign(['sumber_dana_penerima_id']);
            $table->dropForeign(['sumber_dana_pengirim_id']);
            $table->dropColumn(['sumber_dana_pengirim_id', 'sumber_dana_penerima_id']);
        });
    }
};
