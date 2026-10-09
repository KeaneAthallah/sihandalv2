<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\Setting;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\TransferDana;
use App\Models\User;
use App\Services\KasService;
use App\Services\PermintaanDanaService;
use Illuminate\Testing\Fluent\AssertableJson;

uses()->group('api', 'kas-pagu');

function kasPaguFixture(): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $admin = User::factory()->admin()->create();
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'Dana Alokasi Umum (DAU)']);
    $sumberDanaLain = SumberDana::create(['nama_sumber_dana' => 'Dana Alokasi Khusus (DAK)']);

    // Kas masuk 500 ribu pada sumber dana utama.
    $penerimaan = Penerimaan::create(['opd_id' => $opd->id, 'target' => 1000000]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 500000,
        'tanggal' => now(),
    ]);

    $program = Program::create([
        'kode_program' => '1.1', 'nama_program' => 'Program A', 'opd_id' => $opd->id,
    ]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A',
        'pagu' => 0, 'realisasi' => 0,
    ]);
    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id,
        'kode_sub_kegiatan' => '1.1.1.1', 'nama_sub_kegiatan' => 'Sub Kegiatan A',
        'pagu' => 0, 'realisasi' => 0,
    ]);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);
    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id,
        'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id,
        'pagu' => 1000000, 'realisasi' => 0,
    ]);

    return compact(
        'opd', 'user', 'admin', 'sumberDana', 'sumberDanaLain',
        'penerimaan', 'program', 'kegiatan', 'subKegiatan', 'rekening', 'belanja',
    );
}

function permintaanDisetujuiKasPagu(array $f, int $jumlah = 400000): PermintaanDana
{
    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->format('Y'),
        'opd_id' => $f['opd']->id,
        'kegiatan_id' => $f['kegiatan']->id,
        'sub_kegiatan_id' => $f['subKegiatan']->id,
        'belanja_id' => $f['belanja']->id,
        'sumber_dana_id' => $f['sumberDana']->id,
        'sumber_dana' => $f['sumberDana']->nama_sumber_dana,
        'jumlah' => $jumlah,
        'keperluan' => 'Pengadaan barang',
        'status' => 'draft',
    ]);

    app(PermintaanDanaService::class)->submit($permintaan, $f['user']);
    app(PermintaanDanaService::class)->approve($permintaan, $f['admin']);

    return $permintaan;
}

function transferDanaBaru(array $f, int $jumlah = 400000, string $status = 'diproses'): TransferDana
{
    return TransferDana::create([
        'nomor_transfer' => 'TF-'.random_int(1000, 9999).'/'.now()->format('Y'),
        'opd_id' => $f['opd']->id,
        'jumlah' => $jumlah,
        'sumber_dana_pengirim_id' => $f['sumberDana']->id,
        'sumber_dana_penerima_id' => $f['sumberDanaLain']->id,
        'keterangan' => 'Pindah kas antar sumber dana',
        'status' => $status,
        'tanggal' => now(),
    ]);
}

// ---------------------------------------------------------------- pengaturan kuota

test('admin dapat membaca pengaturan melalui api', function () {
    ['admin' => $admin, 'sumberDana' => $sumberDana] = kasPaguFixture();

    $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/settings');

    $response->assertOk();

    $kuota = collect($response->json('data.kuota_penerimaan'))->keyBy('sumber_dana_id');

    expect((float) $kuota[$sumberDana->id]['persen'])->toBe(100.0);
});

test('admin memperbarui kuota penerimaan melalui api', function () {
    ['admin' => $admin, 'sumberDana' => $sumberDana] = kasPaguFixture();

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/settings', ['kuota' => [$sumberDana->id => 50]])
        ->assertOk();

    expect((float) Setting::get(KasService::kuotaKey($sumberDana->id), 100))->toBe(50.0);
});

test('kuota di luar rentang 0-100 ditolak melalui api', function () {
    ['admin' => $admin, 'sumberDana' => $sumberDana] = kasPaguFixture();

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/settings', ['kuota' => [$sumberDana->id => 150]])
        ->assertJsonValidationErrors('kuota.'.$sumberDana->id);
});

test('opd tidak dapat mengakses pengaturan melalui api', function () {
    ['user' => $user, 'sumberDana' => $sumberDana] = kasPaguFixture();

    $this->actingAs($user, 'sanctum')->getJson('/api/v1/settings')->assertForbidden();
    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/settings', ['kuota' => [$sumberDana->id => 50]])
        ->assertForbidden();
});

// ---------------------------------------------------------------- batas kas permintaan dana

test('submit permintaan dana ditolak via api saat kas tidak mencukupi', function () {
    $f = kasPaguFixture();

    // Kas hanya 500 ribu, permintaan 600 ribu.
    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->format('Y'),
        'opd_id' => $f['opd']->id,
        'kegiatan_id' => $f['kegiatan']->id,
        'sub_kegiatan_id' => $f['subKegiatan']->id,
        'belanja_id' => $f['belanja']->id,
        'sumber_dana_id' => $f['sumberDana']->id,
        'sumber_dana' => $f['sumberDana']->nama_sumber_dana,
        'jumlah' => 600000,
        'keperluan' => 'Melebihi kas',
        'status' => 'draft',
    ]);

    $this->actingAs($f['user'], 'sanctum')
        ->postJson("/api/v1/permintaan-dana/{$permintaan->id}/submit")
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('errors.business')
            ->etc()
        );

    expect($permintaan->fresh()->status)->toBe('draft');
});

// ---------------------------------------------------------------- transfer dana

test('admin menyelesaikan transfer dana melalui aksi complete', function () {
    $f = kasPaguFixture();
    $transfer = transferDanaBaru($f);

    $this->actingAs($f['admin'], 'sanctum')
        ->postJson("/api/v1/transfer-dana/{$transfer->id}/complete")
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.status', 'selesai')
            ->etc()
        );

    expect($transfer->fresh()->tanggal_selesai)->not->toBeNull();

    // Kas pengirim berkurang, penerima bertambah.
    $kas = new KasService;
    expect($kas->saldoTersedia($f['opd']->id, $f['sumberDana']->id))->toBe(100000.0)
        ->and($kas->saldoTersedia($f['opd']->id, $f['sumberDanaLain']->id))->toBe(400000.0);
});

test('aksi complete ditolak saat kas pengirim tidak mencukupi', function () {
    $f = kasPaguFixture();
    // Kas hanya 500 ribu.
    $transfer = transferDanaBaru($f, 600000);

    $this->actingAs($f['admin'], 'sanctum')
        ->postJson("/api/v1/transfer-dana/{$transfer->id}/complete")
        ->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('errors.business')
            ->etc()
        );

    expect($transfer->fresh()->status)->toBe('diproses');
});

test('aksi complete hanya untuk admin', function () {
    $f = kasPaguFixture();
    $transfer = transferDanaBaru($f);

    // Rute complete dilindungi middleware admin.
    $this->actingAs($f['user'], 'sanctum')
        ->postJson("/api/v1/transfer-dana/{$transfer->id}/complete")
        ->assertForbidden();

    expect($transfer->fresh()->status)->toBe('diproses');
});

test('update ke status selesai memindahkan saldo sebagai admin', function () {
    $f = kasPaguFixture();
    $transfer = transferDanaBaru($f);

    $this->actingAs($f['admin'], 'sanctum')
        ->putJson("/api/v1/transfer-dana/{$transfer->id}", [
            'opd_id' => $f['opd']->id,
            'jumlah' => 400000,
            'sumber_dana_pengirim_id' => $f['sumberDana']->id,
            'sumber_dana_penerima_id' => $f['sumberDanaLain']->id,
            'keterangan' => 'Pindah kas antar sumber dana',
            'status' => 'selesai',
        ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.status', 'selesai')
            ->etc()
        );

    expect($transfer->fresh()->tanggal_selesai)->not->toBeNull();
});

test('update ke status selesai ditolak untuk opd bukan admin', function () {
    $f = kasPaguFixture();
    $transfer = transferDanaBaru($f);

    $this->actingAs($f['user'], 'sanctum')
        ->putJson("/api/v1/transfer-dana/{$transfer->id}", [
            'opd_id' => $f['opd']->id,
            'jumlah' => 400000,
            'sumber_dana_pengirim_id' => $f['sumberDana']->id,
            'sumber_dana_penerima_id' => $f['sumberDanaLain']->id,
            'keterangan' => 'Pindah kas antar sumber dana',
            'status' => 'selesai',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('status');

    expect($transfer->fresh()->status)->toBe('diproses');
});

test('update ke status selesai ditolak saat kas pengirim kurang', function () {
    $f = kasPaguFixture();
    // Kas hanya 500 ribu.
    $transfer = transferDanaBaru($f, 600000);

    $this->actingAs($f['admin'], 'sanctum')
        ->putJson("/api/v1/transfer-dana/{$transfer->id}", [
            'opd_id' => $f['opd']->id,
            'jumlah' => 600000,
            'sumber_dana_pengirim_id' => $f['sumberDana']->id,
            'sumber_dana_penerima_id' => $f['sumberDanaLain']->id,
            'keterangan' => 'Pindah kas antar sumber dana',
            'status' => 'selesai',
        ])
        ->assertJsonValidationErrors('jumlah');

    expect($transfer->fresh()->status)->toBe('diproses');
});

// ---------------------------------------------------------------- pengeluaran dari permintaan dana

test('admin membuat pengeluaran dari permintaan dana via api', function () {
    $f = kasPaguFixture();
    $permintaan = permintaanDisetujuiKasPagu($f);

    $this->actingAs($f['admin'], 'sanctum')
        ->postJson('/api/v1/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D-001/2026',
            'tanggal_sp2d' => now()->toDateString(),
            'tanggal' => now()->toDateString(),
        ])
        ->assertCreated()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.jumlah', 400000)
            ->where('data.keperluan', 'Pengadaan barang')
            ->where('data.permintaan_dana_id', $permintaan->id)
            ->etc()
        );

    $this->assertDatabaseHas('pengeluarans', [
        'permintaan_dana_id' => $permintaan->id,
        'no_sp2d' => 'SP2D-001/2026',
        'jumlah' => 400000,
    ]);
});

test('field keuangan pengeluaran diambil dari permintaan dana (read-only)', function () {
    $f = kasPaguFixture();
    $permintaan = permintaanDisetujuiKasPagu($f);

    // Nilai jumlah/keperluan yang dikirim harus diabaikan.
    $this->actingAs($f['admin'], 'sanctum')
        ->postJson('/api/v1/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D-002/2026',
            'tanggal_sp2d' => now()->toDateString(),
            'tanggal' => now()->toDateString(),
            'jumlah' => 1,
            'keperluan' => 'input ditolak',
        ])
        ->assertCreated()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('data.jumlah', 400000)
            ->where('data.keperluan', 'Pengadaan barang')
            ->etc()
        );
});

test('opd tidak dapat membuat pengeluaran dari permintaan dana via api', function () {
    $f = kasPaguFixture();
    $permintaan = permintaanDisetujuiKasPagu($f);

    $this->actingAs($f['user'], 'sanctum')
        ->postJson('/api/v1/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D-003/2026',
            'tanggal_sp2d' => now()->toDateString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('permintaan_dana_id');

    expect(Pengeluaran::count())->toBe(0);
});

test('pengeluaran ditolak untuk permintaan dana yang belum disetujui', function () {
    $f = kasPaguFixture();
    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->format('Y'),
        'opd_id' => $f['opd']->id,
        'sumber_dana_id' => $f['sumberDana']->id,
        'sumber_dana' => $f['sumberDana']->nama_sumber_dana,
        'jumlah' => 400000,
        'keperluan' => 'Masih draft',
        'status' => 'draft',
    ]);

    $this->actingAs($f['admin'], 'sanctum')
        ->postJson('/api/v1/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D-004/2026',
            'tanggal_sp2d' => now()->toDateString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('permintaan_dana_id');

    expect(Pengeluaran::count())->toBe(0);
});

test('pengeluaran tidak dapat dibuat dua kali untuk permintaan dana yang sama', function () {
    $f = kasPaguFixture();
    $permintaan = permintaanDisetujuiKasPagu($f);

    $this->actingAs($f['admin'], 'sanctum')
        ->postJson('/api/v1/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D-005/2026',
            'tanggal_sp2d' => now()->toDateString(),
        ])
        ->assertCreated();

    $this->actingAs($f['admin'], 'sanctum')
        ->postJson('/api/v1/pengeluaran', [
            'permintaan_dana_id' => $permintaan->id,
            'no_sp2d' => 'SP2D-006/2026',
            'tanggal_sp2d' => now()->toDateString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('permintaan_dana_id');

    expect(Pengeluaran::where('permintaan_dana_id', $permintaan->id)->count())->toBe(1);
});
