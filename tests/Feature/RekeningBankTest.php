<?php

use App\Models\Opd;
use App\Models\RekeningBank;
use App\Models\User;

test('admin can store a rekening bank via the pop-up endpoint', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->postJson('/rekening-bank', [
            'bank_name' => 'Bank BRI',
            'account_number' => '0010-01-000123-7',
            'account_name' => 'Dinas A',
        ])
        ->assertOk()
        ->assertJsonStructure(['id', 'label', 'message']);

    $this->assertDatabaseHas('rekening_banks', [
        'bank_name' => 'Bank BRI',
        'account_number' => '0010-01-000123-7',
        'account_name' => 'Dinas A',
        'is_active' => true,
    ]);

    expect(RekeningBank::find($response->json('id'))->label)
        ->toBe('Bank BRI — 0010-01-000123-7 — Dinas A');
});

test('opd user can store a rekening bank (global master, no opd scoping)', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user)
        ->postJson('/rekening-bank', [
            'bank_name' => 'Bank Mandiri',
            'account_number' => '1370-01-000456-8',
            'account_name' => 'Dinas B',
        ])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Rekening bank berhasil ditambahkan.']);

    expect(RekeningBank::count())->toBe(1);
});

test('account number must be unique globally across all banks', function () {
    $admin = User::factory()->admin()->create();

    RekeningBank::create([
        'bank_name' => 'Bank BRI',
        'account_number' => '0010-01-000123-7',
        'account_name' => 'Dinas A',
    ]);

    $this->actingAs($admin)
        ->from('/transaksi-penerimaan/create')
        ->post('/rekening-bank', [
            'bank_name' => 'Bank BNI',
            'account_number' => '0010-01-000123-7',
            'account_name' => 'Dinas B',
        ])
        ->assertSessionHasErrors('account_number');

    expect(session('errors')->first('account_number'))->toBe('Nomor rekening bank sudah digunakan.');
    expect(RekeningBank::count())->toBe(1);
});

test('duplicate account number is rejected on update but self-update keeps the number', function () {
    $admin = User::factory()->admin()->create();

    $bank = RekeningBank::create([
        'bank_name' => 'Bank BRI',
        'account_number' => '0010-01-000123-7',
        'account_name' => 'Dinas A',
    ]);
    $other = RekeningBank::create([
        'bank_name' => 'Bank BNI',
        'account_number' => '0091-01-000789-9',
        'account_name' => 'Dinas A',
    ]);

    $this->actingAs($admin)
        ->from("/transaksi-penerimaan/{$bank->id}/edit")
        ->put("/rekening-bank/{$bank->id}", [
            'bank_name' => 'Bank BRI',
            'account_number' => $other->account_number,
            'account_name' => 'Dinas A',
        ])
        ->assertSessionHasErrors('account_number');

    $this->actingAs($admin)
        ->put("/rekening-bank/{$bank->id}", [
            'bank_name' => 'Bank BRI',
            'account_number' => $bank->account_number,
            'account_name' => 'Dinas A',
        ])
        ->assertSessionHasNoErrors();

    expect($bank->fresh()->account_number)->toBe('0010-01-000123-7');
});

test('update can change the bank label and active state', function () {
    $admin = User::factory()->admin()->create();

    $bank = RekeningBank::create([
        'bank_name' => 'Bank BRI',
        'account_number' => '0010-01-000123-7',
        'account_name' => 'Dinas A',
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->putJson("/rekening-bank/{$bank->id}", [
            'bank_name' => 'Bank BRI',
            'account_number' => '0010-01-000123-7',
            'account_name' => 'Dinas Kesehatan',
            'is_active' => false,
        ])
        ->assertOk();

    $bank->refresh();
    expect($bank->account_name)->toBe('Dinas Kesehatan')
        ->and($bank->is_active)->toBeFalse()
        ->and($bank->label)->toBe('Bank BRI — 0010-01-000123-7 — Dinas Kesehatan');
});

test('a non-JSON store request redirects back with a success flash', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->from('/keuangan')
        ->post('/rekening-bank', [
            'bank_name' => 'Bank BRI',
            'account_number' => '0010-01-000123-7',
            'account_name' => 'Dinas A',
        ])
        ->assertRedirect('/keuangan')
        ->assertSessionHas('success', 'Rekening bank berhasil ditambahkan.');
});

test('label accessor formats bank name, number, and account name', function () {
    $bank = RekeningBank::create([
        'bank_name' => 'Bank Mandiri',
        'account_number' => '1370-01-000456-8',
        'account_name' => 'Dinas B',
    ]);

    expect($bank->label)->toBe('Bank Mandiri — 1370-01-000456-8 — Dinas B');
});
