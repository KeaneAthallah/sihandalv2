<?php

namespace App\Console\Commands;

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use Illuminate\Console\Command;

class BackfillKegiatanLegacy extends Command
{
    protected $signature = 'app:backfill-kegiatan-legacy {--chunk=500 : Rows per chunk}';

    protected $description = 'Fill legacy flat columns (kode/nama_sub_kegiatan, kode/nama_rekening, rekening_id) on kegiatan from its first sub-kegiatan and belanja. Idempotent; only fills NULL columns.';

    public function handle(): int
    {
        $chunk = max(1, (int) $this->option('chunk'));

        $stats = [
            'filled' => 0,
            'no_sub_kegiatan' => 0,
            'no_belanja' => 0,
        ];

        $this->info('Backfilling kegiatan legacy columns from sub_kegiatans/belanjas…');

        Kegiatan::whereNull('kode_sub_kegiatan')
            ->orderBy('id')
            ->chunkById($chunk, function ($kegiatans) use (&$stats): void {
                foreach ($kegiatans as $kegiatan) {
                    $subKegiatan = SubKegiatan::query()
                        ->where('kegiatan_id', $kegiatan->id)
                        ->orderBy('id')
                        ->first();

                    if ($subKegiatan === null) {
                        $stats['no_sub_kegiatan']++;

                        continue;
                    }

                    $belanja = Belanja::query()
                        ->where('sub_kegiatan_id', $subKegiatan->id)
                        ->orderBy('id')
                        ->first();

                    if ($belanja === null) {
                        $stats['no_belanja']++;

                        continue;
                    }

                    $kegiatan->update([
                        'kode_sub_kegiatan' => $subKegiatan->kode_sub_kegiatan,
                        'nama_sub_kegiatan' => $subKegiatan->nama_sub_kegiatan,
                        'rekening_id' => $belanja->rekening_id,
                        'kode_rekening' => $belanja->rekening?->kode,
                        'nama_rekening' => $belanja->rekening?->nama,
                    ]);

                    $stats['filled']++;
                }
            });

        $this->renderReport($stats);

        return 0;
    }

    private function renderReport(array $stats): void
    {
        $this->newLine();
        $this->info('=== Backfill legacy kegiatan columns ===');
        $this->table(
            ['Item', 'Count'],
            [
                ['Kegiatan filled', number_format($stats['filled'])],
                ['Skipped (no sub kegiatan)', number_format($stats['no_sub_kegiatan'])],
                ['Skipped (no belanja)', number_format($stats['no_belanja'])],
            ]
        );

        if ($stats['filled'] > 0) {
            $this->warn('Legacy columns hold the FIRST sub-kegiatan/rekening only — each kegiatan may have many. The full 1:N data stays in sub_kegiatans/belanjas.');
        }
    }
}
