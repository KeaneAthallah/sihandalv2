<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Kuota penerimaan kini disimpan per pasangan OPD + sumber dana
     * (key "penerimaan_kuota_persen.{opd}.{sumber_dana}"), sehingga
     * key global lama tidak lagi dipakai.
     */
    public function up(): void
    {
        DB::table('settings')->where('key', 'penerimaan_kuota_persen')->delete();
    }

    public function down(): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => 'penerimaan_kuota_persen'],
            ['value' => '100', 'created_at' => now(), 'updated_at' => now()],
        );
    }
};
