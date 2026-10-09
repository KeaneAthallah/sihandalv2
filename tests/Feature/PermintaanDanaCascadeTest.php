<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\User;

test('create page lists programs under the opd of their kegiatans', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    // Imported programs are global (programs.opd_id is null);
    // the OPD link comes from the kegiatan rows.
    $program = Program::create(['kode_program' => '1.01.1', 'nama_program' => 'Program Air']);
    Kegiatan::create([
        'program_id' => $program->id,
        'opd_id' => $opd->id,
        'kode_kegiatan' => '1.01.1.01',
        'nama_kegiatan' => 'Kegiatan Air',
    ]);

    // A program without any kegiatan has nothing to request funds against.
    Program::create(['kode_program' => '9.9.9', 'nama_program' => 'Program Tanpa Kegiatan']);

    $this->actingAs($admin)
        ->get(route('permintaan-dana.create'))
        ->assertSuccessful()
        ->assertSee('1.01.1 - Program Air')
        ->assertDontSee('9.9.9 - Program Tanpa Kegiatan')
        // Sumber dana cascade wiring: gated behind belanja,
        // auto-filled from it, with the usable amount shown.
        ->assertSee('sumberDanaId')
        ->assertSee('sumberDanaList')
        ->assertSee('sumberDanaOptions')
        ->assertSee('jumlahTersedia')
        ->assertSee('jumlahInput')
        ->assertSee(':disabled="!belanjaId"', false)
        ->assertSee('onBelanjaChange($event.target.value)', false)
        // selectedBelanja resolves this inside the find
        // callback via the thisArg argument.
        ->assertSee('this.belanjaId; }, this)', false)
        ->assertSee('"sumber_dana_id"', false)
        // Jumlah slider bounded by the available amount.
        ->assertSee('type="range"', false)
        ->assertSee('x-model.number="jumlahInput"', false);
});

test('create page cascade scopes kegiatans to the opd for opd users', function () {
    $opdA = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $opdB = Opd::create(['kode' => 'OPD-B', 'nama' => 'Dinas B']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opdA->id]);

    $program = Program::create(['kode_program' => '1.02.1', 'nama_program' => 'Program Bersama']);
    Kegiatan::create([
        'program_id' => $program->id,
        'opd_id' => $opdA->id,
        'kode_kegiatan' => '1.02.1.01',
        'nama_kegiatan' => 'Kegiatan OPD A',
    ]);
    Kegiatan::create([
        'program_id' => $program->id,
        'opd_id' => $opdB->id,
        'kode_kegiatan' => '1.02.1.02',
        'nama_kegiatan' => 'Kegiatan OPD B',
    ]);

    $this->actingAs($user)
        ->get(route('permintaan-dana.create'))
        ->assertSuccessful()
        ->assertSee('1.02.1.01 - Kegiatan OPD A')
        ->assertDontSee('1.02.1.02 - Kegiatan OPD B');
});

test('rekening permintaan dana mengikuti rekening belanja', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);
    $rekeningLain = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);

    $program = Program::create(['kode_program' => '1.01.1', 'nama_program' => 'Program A']);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'kode_kegiatan' => '1.01.1.01', 'nama_kegiatan' => 'Kegiatan A',
    ]);
    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id,
        'kode_sub_kegiatan' => '1.01.1.01.001', 'nama_sub_kegiatan' => 'Sub A',
    ]);
    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id,
        'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id,
        'pagu' => 1000000,
    ]);

    // Even a deliberately mismatched manual pick is overridden
    // — the server always trusts the belanja's rekening.
    $this->actingAs($admin)
        ->post(route('permintaan-dana.store'), [
            'opd_id' => $opd->id,
            'sumber_dana_id' => $sumberDana->id,
            'kegiatan_id' => $kegiatan->id,
            'sub_kegiatan_id' => $subKegiatan->id,
            'belanja_id' => $belanja->id,
            'rekening_id' => $rekeningLain->id,
            'jumlah' => 100000,
            'keperluan' => 'Operasional',
        ])
        ->assertRedirect();

    $permintaan = PermintaanDana::first();

    expect($permintaan)->not->toBeNull()
        ->and($permintaan->rekening_id)->toBe($belanja->rekening_id);
});

test('edit page prefills the cascade with sumber dana gating', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.1.1', 'nama' => 'Belanja Pegawai', 'tipe' => 'belanja']);

    $program = Program::create(['kode_program' => '1.01.1', 'nama_program' => 'Program A']);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'kode_kegiatan' => '1.01.1.01', 'nama_kegiatan' => 'Kegiatan A',
    ]);
    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id,
        'kode_sub_kegiatan' => '1.01.1.01.001', 'nama_sub_kegiatan' => 'Sub A',
    ]);
    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id,
        'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id,
        'pagu' => 1000000,
    ]);
    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $kegiatan->id,
        'sub_kegiatan_id' => $subKegiatan->id,
        'belanja_id' => $belanja->id,
        'rekening_id' => $belanja->rekening_id,
        'jumlah' => 100000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($admin)
        ->get(route('permintaan-dana.edit', $permintaan))
        ->assertSuccessful()
        ->assertSee('sumberDanaId')
        ->assertSee('sumberDanaList')
        ->assertSee('sumberDanaOptions')
        ->assertSee('jumlahTersedia')
        ->assertSee('jumlahInput')
        ->assertSee(':disabled="!belanjaId"', false)
        ->assertSee('onBelanjaChange($event.target.value)', false)
        // selectedBelanja resolves this inside the find
        // callback via the thisArg argument.
        ->assertSee('this.belanjaId; }, this)', false)
        ->assertSee('"sumber_dana_id"', false)
        ->assertSee('type="range"', false)
        ->assertSee('x-model.number="jumlahInput"', false)
        ->assertSee('\u0022pagu\u0022', false)
        ->assertSee('\u0022penerimaan\u0022', false)
        ->assertSee('\u0022dana_di_commit\u0022', false);
});
