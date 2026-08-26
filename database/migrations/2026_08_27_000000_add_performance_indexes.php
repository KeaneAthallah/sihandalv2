<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_danas', function (Blueprint $table) {
            $table->index(['status', 'opd_id']);
        });

        Schema::table('sumber_danas', function (Blueprint $table) {
            $table->index(['opd_id', 'nama_sumber_dana']);
        });

        Schema::table('penerimaans', function (Blueprint $table) {
            $table->index(['opd_id', 'tanggal']);
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->index(['opd_id', 'tanggal']);
        });

        Schema::table('posisi_kas', function (Blueprint $table) {
            $table->index(['opd_id', 'tanggal']);
        });

        Schema::table('transfer_danas', function (Blueprint $table) {
            $table->index(['opd_id', 'status']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_danas', function (Blueprint $table) {
            $table->dropIndex(['status', 'opd_id']);
        });

        Schema::table('sumber_danas', function (Blueprint $table) {
            $table->dropIndex(['opd_id', 'nama_sumber_dana']);
        });

        Schema::table('penerimaans', function (Blueprint $table) {
            $table->dropIndex(['opd_id', 'tanggal']);
        });

        Schema::table('pengeluarans', function (Blueprint $table) {
            $table->dropIndex(['opd_id', 'tanggal']);
        });

        Schema::table('posisi_kas', function (Blueprint $table) {
            $table->dropIndex(['opd_id', 'tanggal']);
        });

        Schema::table('transfer_danas', function (Blueprint $table) {
            $table->dropIndex(['opd_id', 'status']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
        });
    }
};
