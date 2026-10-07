<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\PermintaanDana;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\User;
use App\Notifications\PermintaanDanaNotification;

test('admin can export laporan penerimaan as csv', function () {
    $d = seedFullDataset();
    $this->actingAs($d['admin']);

    $response = $this->get(route('laporan-penerimaan.export'));

    $response->assertSuccessful()
        ->assertHeaderContains('Content-Type', 'text/csv')
        ->assertHeaderContains('Content-Disposition', 'laporan-penerimaan');
});

test('admin can export laporan pengeluaran as csv', function () {
    $d = seedFullDataset();
    $this->actingAs($d['admin']);

    $response = $this->get(route('laporan-pengeluaran.export'));

    $response->assertSuccessful()
        ->assertHeaderContains('Content-Type', 'text/csv')
        ->assertHeaderContains('Content-Disposition', 'laporan-pengeluaran');
});

test('admin can export laporan posisi kas as csv', function () {
    $d = seedFullDataset();
    $this->actingAs($d['admin']);

    $response = $this->get(route('laporan-posisi-kas.export'));

    $response->assertSuccessful()
        ->assertHeaderContains('Content-Type', 'text/csv')
        ->assertHeaderContains('Content-Disposition', 'laporan-posisi-kas');
});

test('admin can export rekap permintaan dana as csv', function () {
    $d = seedFullDataset();
    $this->actingAs($d['admin']);

    $response = $this->get(route('rekap-permintaan-dana.export'));

    $response->assertSuccessful()
        ->assertHeaderContains('Content-Type', 'text/csv')
        ->assertHeaderContains('Content-Disposition', 'rekap-permintaan-dana');
});

test('opd user export is scoped to their opd', function () {
    $d = seedFullDataset();
    $this->actingAs($d['user']);

    $response = $this->get(route('laporan-penerimaan.export'));

    $response->assertSuccessful();
    $content = $response->streamedContent();
    expect($content)->toContain('Dinas A');
    expect($content)->not->toContain('Dinas B');
});

test('submit permintaan dana sends notification to admins', function () {
    Notification::fake();

    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A', 'total_pagu' => 2000000000]);
    $admin = User::factory()->admin()->create();
    $user = User::factory()->opd()->create(['opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);

    $program = Program::create(['kode_program' => '1.1', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id, 'kode_kegiatan' => '1.1.1', 'nama_kegiatan' => 'Kegiatan A',
    ]);
    $subKegiatan = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.1.1.1', 'nama_sub_kegiatan' => 'Sub A',
    ]);
    $belanja = Belanja::create([
        'sub_kegiatan_id' => $subKegiatan->id, 'rekening_id' => $rekening->id, 'sumber_dana_id' => $sumberDana->id,
        'opd_id' => $opd->id, 'pagu' => 10000000000, 'realisasi' => 0, 'dana_di_commit' => 0,
    ]);

    // Kas masuk agar permintaan dana dapat
    // dikomit (tersedia = min(pagu, kas)).
    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'target' => 10000000000,
    ]);
    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id,
        'sumber_dana_id' => $sumberDana->id,
        'realisasi' => 50000000,
        'tanggal' => now(),
    ]);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-TEST/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'kegiatan_id' => $kegiatan->id,
        'sub_kegiatan_id' => $subKegiatan->id,
        'belanja_id' => $belanja->id,
        'jumlah' => 50000000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user)
        ->post("/permintaan-dana/{$permintaan->id}/submit");

    Notification::assertSentTo(
        [$admin],
        PermintaanDanaNotification::class,
    );
});

test('approve permintaan dana sends notification to opd users', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();
    $user = User::factory()->opd()->create(['opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-APPROVE/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 30000000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($admin);

    $this->post(route('persetujuan.setujui', $permintaan));

    $this->assertDatabaseHas('notifications', [
        'type' => PermintaanDanaNotification::class,
        'notifiable_id' => $user->id,
    ]);
});

test('reject permintaan dana sends notification to opd users', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $admin = User::factory()->admin()->create();
    $user = User::factory()->opd()->create(['opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-REJECT/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 30000000,
        'keperluan' => 'Operasional',
        'status' => 'menunggu',
    ]);

    $this->actingAs($admin);

    $this->post(route('persetujuan.tolak', $permintaan));

    $this->assertDatabaseHas('notifications', [
        'type' => PermintaanDanaNotification::class,
        'notifiable_id' => $user->id,
    ]);
});

test('user can mark notification as read', function () {
    $user = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-READ/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 10000000,
        'keperluan' => 'Test',
        'status' => 'menunggu',
    ]);

    $notification = $user->notifyNow(new PermintaanDanaNotification(
        $permintaan,
        'Test Notifikasi',
        'Ini adalah notifikasi test',
        '/notifications',
    ));

    $dbNotification = $user->notifications()->first();
    $this->assertNull($dbNotification->read_at);

    $this->actingAs($user)
        ->post(route('notifications.markAsRead', $dbNotification));

    $this->assertDatabaseHas('notifications', [
        'id' => $dbNotification->id,
    ]);
    expect($dbNotification->fresh()->read_at)->not->toBeNull();
});

test('user can mark all notifications as read', function () {
    $user = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-ALL/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 10000000,
        'keperluan' => 'Test',
        'status' => 'menunggu',
    ]);

    $user->notify(new PermintaanDanaNotification(
        $permintaan,
        'Notifikasi 1',
        'Pesan 1',
        '/notifications',
    ));
    $user->notify(new PermintaanDanaNotification(
        $permintaan,
        'Notifikasi 2',
        'Pesan 2',
        '/notifications',
    ));

    expect($user->unreadNotifications()->count())->toBe(2);

    $this->actingAs($user)
        ->post(route('notifications.markAllAsRead'));

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});
