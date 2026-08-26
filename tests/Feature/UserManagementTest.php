<?php

use App\Models\Opd;
use App\Models\User;

test('guest is redirected to login when visiting user management', function () {
    $this->get('/user-management')->assertRedirect('/login');
});

test('opd user cannot access user management', function () {
    $opd = Opd::create(['kode' => 'OPD001', 'nama' => 'Dinas Test']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user)
        ->get('/user-management')
        ->assertForbidden();
});

test('admin can view user management page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/user-management')
        ->assertSuccessful();
});

test('admin can create an opd user', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD001', 'nama' => 'Dinas Pendidikan']);

    $this->actingAs($admin)
        ->post('/user-management', [
            'name' => 'Budi OPD',
            'email' => 'budi@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'opd',
            'opd_id' => $opd->id,
        ])
        ->assertRedirect(route('user-management.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'budi@example.com',
        'role' => 'opd',
        'opd_id' => $opd->id,
    ]);
});

test('opd role requires an opd selection', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/user-management', [
            'name' => 'Budi OPD',
            'email' => 'budi@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'opd',
            'opd_id' => '',
        ])
        ->assertSessionHasErrors('opd_id');

    $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
});

test('admin can create an admin user without opd', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/user-management', [
            'name' => 'Admin Baru',
            'email' => 'admin2@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'admin',
            'opd_id' => '',
        ])
        ->assertRedirect(route('user-management.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'admin2@example.com',
        'role' => 'admin',
        'opd_id' => null,
    ]);
});

test('register route is not available anymore', function () {
    $this->get('/register')->assertNotFound();
});
