<?php

use App\Models\Belanja;
use App\Models\Dinas;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\Unit;
use App\Models\Upt;
use App\Models\User;

test('opd hierarchy creates dinas, unit, and upt', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $dinas = Dinas::create(['kode' => 'DIN-1', 'nama' => 'Dinas Pendidikan', 'opd_id' => $opd->id]);
    $unit = Unit::create(['kode' => 'UNIT-1', 'nama' => 'Unit SMP', 'opd_id' => $opd->id, 'dinas_id' => $dinas->id]);
    $upt = Upt::create(['kode' => 'UPT-1', 'nama' => 'UPT Lab', 'opd_id' => $opd->id]);

    expect($opd->dinasList->pluck('id'))->toContain($dinas->id)
        ->and($opd->units->pluck('id'))->toContain($unit->id)
        ->and($opd->upts->pluck('id'))->toContain($upt->id)
        ->and($opd->nmskpd)->toBeNull();
});

test('belanja aggregates into sub kegiatan, kegiatan, and program hierarchy', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja', 'saldo' => 0]);

    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'kode_kegiatan' => '1.2.3', 'nama_kegiatan' => 'Kegiatan A', 'pagu' => 0, 'realisasi' => 0,
    ]);

    $sub1 = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.2.3.1', 'nama_sub_kegiatan' => 'Sub 1',
        'pagu' => 0, 'realisasi' => 0,
    ]);
    $sub2 = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.2.3.2', 'nama_sub_kegiatan' => 'Sub 2',
        'pagu' => 0, 'realisasi' => 0,
    ]);

    Belanja::create(['sub_kegiatan_id' => $sub1->id, 'rekening_id' => $rekening->id, 'sumber_dana_id' => $sumberDana->id, 'opd_id' => $opd->id, 'pagu' => 1000000, 'realisasi' => 200000, 'dana_di_commit' => 0]);
    Belanja::create(['sub_kegiatan_id' => $sub1->id, 'rekening_id' => $rekening->id, 'sumber_dana_id' => $sumberDana->id, 'opd_id' => $opd->id, 'pagu' => 1500000, 'realisasi' => 0, 'dana_di_commit' => 0]);
    Belanja::create(['sub_kegiatan_id' => $sub2->id, 'rekening_id' => $rekening->id, 'sumber_dana_id' => $sumberDana->id, 'opd_id' => $opd->id, 'pagu' => 500000, 'realisasi' => 100000, 'dana_di_commit' => 0]);

    expect((float) $sub1->belanjas()->sum('pagu'))->toBe(2500000.0)
        ->and((float) $sub1->belanjas()->sum('realisasi'))->toBe(200000.0)
        ->and($kegiatan->subKegiatans()->count())->toBe(2)
        ->and($opd->belanjas()->count())->toBe(3)
        ->and((float) $opd->belanjas()->sum('pagu'))->toBe(3000000.0)
        ->and($kegiatan->belanjas()->count())->toBe(3);
});

test('belanja has unavailable fund protection on commit', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja', 'saldo' => 0]);
    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create(['program_id' => $program->id, 'opd_id' => $opd->id, 'kode_kegiatan' => '1.2.3', 'nama_kegiatan' => 'Kegiatan A', 'pagu' => 0, 'realisasi' => 0]);
    $sub = SubKegiatan::create(['kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.2.3.1', 'nama_sub_kegiatan' => 'Sub 1', 'pagu' => 0, 'realisasi' => 0]);

    $belanja = Belanja::create(['sub_kegiatan_id' => $sub->id, 'rekening_id' => $rekening->id, 'sumber_dana_id' => $sumberDana->id, 'opd_id' => $opd->id, 'pagu' => 1000000, 'realisasi' => 0, 'dana_di_commit' => 0]);

    $belanja->commit(400000);
    expect((float) $belanja->fresh()->dana_di_commit)->toBe(400000.0)
        ->and((float) $belanja->fresh()->availablePagu())->toBe(600000.0);

    $belanja->releaseCommit(400000);
    expect((float) $belanja->fresh()->dana_di_commit)->toBe(0.0);

    $belanja->commit(300000);
    $belanja->realize(300000);
    expect((float) $belanja->fresh()->realisasi)->toBe(300000.0)
        ->and((float) $belanja->fresh()->dana_di_commit)->toBe(0.0)
        ->and((float) $belanja->fresh()->availablePagu())->toBe(700000.0);

    expect(fn () => $belanja->commit(999999999))->toThrow(RuntimeException::class, 'melebihi pagu');
});

test('opd user cannot access sub-kegiatan of another opd', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A', 'opd_id' => $opdB->id]);
    $kegiatanB = Kegiatan::create(['program_id' => $program->id, 'opd_id' => $opdB->id, 'kode_kegiatan' => '1.2.3', 'nama_kegiatan' => 'Kegiatan B', 'pagu' => 0, 'realisasi' => 0]);

    $userA = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $this->actingAs($userA)
        ->get("/kegiatan/{$kegiatanB->id}/sub-kegiatan")
        ->assertForbidden();
});
