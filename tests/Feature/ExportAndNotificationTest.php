<?php

use App\Models\Opd;
use App\Models\PermintaanDana;
use App\Models\SumberDana;
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
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A', 'total_pagu' => 2000000000]);
    $admin = User::factory()->admin()->create();
    $user = User::factory()->opd()->create(['opd_id' => $opd->id]);
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);

    $permintaan = PermintaanDana::create([
        'nomor_permintaan' => 'PD-TEST/'.now()->year,
        'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id,
        'sumber_dana' => 'DAU',
        'jumlah' => 50000000,
        'keperluan' => 'Operasional',
        'status' => 'draft',
    ]);

    $this->actingAs($user);

    $this->post(route('permintaan-dana.submit', $permintaan));

    $this->assertDatabaseHas('notifications', [
        'type' => PermintaanDanaNotification::class,
        'notifiable_id' => $admin->id,
    ]);
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
