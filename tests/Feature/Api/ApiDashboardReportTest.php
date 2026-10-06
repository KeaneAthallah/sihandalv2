<?php

use App\Models\Belanja;
use App\Models\Kegiatan;
use App\Models\Opd;
use App\Models\Penerimaan;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\PosisiKas;
use App\Models\Program;
use App\Models\Rekening;
use App\Models\SubKegiatan;
use App\Models\SumberDana;
use App\Models\TransaksiPenerimaan;
use App\Models\TransferDana;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notification;

uses()->group('api');

function apiReportFixture(): array
{
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $admin = User::factory()->admin()->create();
    $sumberDana = SumberDana::create(['nama_sumber_dana' => 'DAU']);
    $rekening = Rekening::create(['kode' => '5.2.1', 'nama' => 'Belanja Jasa', 'tipe' => 'belanja']);
    $kasRekening = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Umum', 'tipe' => 'kas']);

    $program = Program::create(['kode_program' => '1.2', 'nama_program' => 'Program A', 'opd_id' => $opd->id]);
    $kegiatan = Kegiatan::create([
        'program_id' => $program->id, 'opd_id' => $opd->id,
        'kode_kegiatan' => '1.2.3', 'nama_kegiatan' => 'Kegiatan A', 'pagu' => 0, 'realisasi' => 0,
    ]);
    $sub = SubKegiatan::create([
        'kegiatan_id' => $kegiatan->id, 'kode_sub_kegiatan' => '1.2.3.1', 'nama_sub_kegiatan' => 'Sub A',
        'pagu' => 0, 'realisasi' => 0,
    ]);

    $belanja = Belanja::create([
        'sub_kegiatan_id' => $sub->id, 'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id, 'opd_id' => $opd->id,
        'pagu' => 1000000, 'realisasi' => 400000, 'dana_di_commit' => 100000,
    ]);

    $penerimaan = Penerimaan::create([
        'opd_id' => $opd->id, 'rekening_id' => $kasRekening->id,
        'target' => 800000,
    ]);

    TransaksiPenerimaan::create([
        'penerimaan_id' => $penerimaan->id, 'nomor_registrasi' => 'REG-00001/'.now()->year,
        'realisasi' => 300000, 'tanggal' => now(),
    ]);

    Pengeluaran::create([
        'opd_id' => $opd->id, 'rekening_id' => $rekening->id,
        'sumber_dana_id' => $sumberDana->id, 'sumber_dana' => 'DAU',
        'jumlah' => 200000, 'keperluan' => 'Operasional', 'tanggal' => now(),
    ]);

    PosisiKas::create([
        'opd_id' => $opd->id, 'tanggal' => now(),
        'nama_rekening' => 'Kas Umum', 'nomor_rekening' => '001-000-1',
        'saldo' => 130000,
    ]);

    PermintaanDana::create([
        'nomor_permintaan' => 'PD-0001/'.now()->year, 'opd_id' => $opd->id,
        'sumber_dana_id' => $sumberDana->id, 'sumber_dana' => 'DAU',
        'jumlah' => 100000, 'keperluan' => 'Operasional', 'status' => 'menunggu',
    ]);

    $sumberDanaB = SumberDana::create(['nama_sumber_dana' => 'DAK']);

    TransferDana::create([
        'nomor_transfer' => 'TF-0001/'.now()->year, 'opd_id' => $opd->id,
        'jumlah' => 50000,
        'sumber_dana_pengirim_id' => $sumberDanaB->id,
        'sumber_dana_penerima_id' => $sumberDana->id,
        'status' => 'diproses',
    ]);

    return compact('opd', 'user', 'admin', 'belanja', 'penerimaan');
}

test('dashboard budget endpoint returns aggregate totals', function (): void {
    ['user' => $user] = apiReportFixture();

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/dashboard/budget')
        ->assertOk()
        ->json('data');

    expect($data['pagu'])->toBe(1000000)
        ->and($data['realisasi'])->toBe(400000)
        ->and($data['commit'])->toBe(100000)
        ->and($data['available'])->toBe(500000)
        ->and($data['percentage'])->toBe(40);
});

test('dashboard revenue and expenditure endpoints return totals', function (): void {
    ['user' => $user] = apiReportFixture();

    expect($this->actingAs($user, 'sanctum')->getJson('/api/v1/dashboard/revenue')->json('data.total_penerimaan'))->toBe(300000)
        ->and($this->actingAs($user, 'sanctum')->getJson('/api/v1/dashboard/expenditure')->json('data.total_pengeluaran'))->toBe(200000);
});

test('dashboard programs endpoint returns derived per-program totals', function (): void {
    ['user' => $user] = apiReportFixture();

    $programs = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/dashboard/programs')
        ->assertOk()
        ->json('data');

    expect(count($programs))->toBe(1)
        ->and($programs[0]['pagu'])->toBe(1000000)
        ->and($programs[0]['realisasi'])->toBe(400000);
});

test('report penerimaan returns rows with summary meta', function (): void {
    ['user' => $user] = apiReportFixture();

    $payload = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reports/penerimaan')
        ->assertOk()
        ->json();

    expect($payload['success'])->toBeTrue()
        ->and($payload['meta']['total_target'])->toBe(800000)
        ->and($payload['meta']['total_realisasi'])->toBe(300000)
        ->and($payload['meta']['persentase'])->toBe(37.5);
});

test('report pengeluaran returns rows with summary meta', function (): void {
    ['user' => $user] = apiReportFixture();

    $payload = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reports/pengeluaran')
        ->assertOk()
        ->json();

    expect($payload['meta']['total_jumlah'])->toBe(200000);
});

test('report permintaan dana returns status breakdown', function (): void {
    ['user' => $user] = apiReportFixture();

    $payload = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reports/permintaan-dana')
        ->assertOk()
        ->json();

    expect($payload['meta']['total_menunggu'])->toBe(100000)
        ->and($payload['meta']['total_permintaan'])->toBe(100000);
});

test('report posisi kas returns totals', function (): void {
    ['user' => $user] = apiReportFixture();

    $payload = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reports/posisi-kas')
        ->assertOk()
        ->json();

    expect($payload['meta']['total_saldo'])->toBe(130000);
});

test('csv export endpoints stream a download', function (): void {
    ['user' => $user] = apiReportFixture();

    $response = $this->actingAs($user, 'sanctum')
        ->get('/api/v1/reports/penerimaan/export');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/csv')
        ->and($response->headers->get('Content-Disposition'))->toContain('laporan-penerimaan');
});

test('ai summary returns structured factual data', function (): void {
    ['user' => $user, 'opd' => $opd] = apiReportFixture();

    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/ai/summary')
        ->assertOk()
        ->json('data');

    expect($data['opd']['id'])->toBe($opd->id)
        ->and($data['budget']['pagu'])->toBe(1000000)
        ->and($data['budget']['available'])->toBe(500000)
        ->and($data['fund_requests']['menunggu'])->toBe(1);
});

test('ai financial alerts derive from existing data rules', function (): void {
    ['user' => $user] = apiReportFixture();

    $alerts = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/ai/financial-alerts')
        ->assertOk()
        ->json('data');

    $types = collect($alerts)->pluck('type')->all();

    expect($types)->toContain('pending_fund_requests')
        ->and($types)->toContain('transfers_in_progress');
});

test('ai endpoints are protected by authentication', function (): void {
    apiReportFixture();

    $this->getJson('/api/v1/ai/summary')->assertStatus(401);
    $this->getJson('/api/v1/ai/financial-alerts')->assertStatus(401);
});

test('notifications are scoped to the authenticated user', function (): void {
    ['user' => $user, 'admin' => $admin] = apiReportFixture();

    $admin->notify(new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['database'];
        }

        public function toArray($notifiable): array
        {
            return ['title' => 'Admin only'];
        }
    });

    // User's own list is empty; the admin notification is not visible.
    $data = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notifications')
        ->assertOk()
        ->json('data');

    expect(count($data))->toBe(0);

    // Admin marks their notification read via the API.
    $notification = DatabaseNotification::query()->first();
    $this->actingAs($admin, 'sanctum')
        ->postJson("/api/v1/notifications/{$notification->id}/read")
        ->assertOk();

    // Another user cannot touch someone else's notification.
    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/notifications/{$notification->id}/read")
        ->assertForbidden();
});
