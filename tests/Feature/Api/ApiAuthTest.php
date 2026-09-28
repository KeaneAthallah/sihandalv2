<?php

use App\Models\Opd;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

uses()->group('api');

test('health endpoint is public and reports application status', function (): void {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('success', true)
            ->where('data.status', 'ok')
            ->where('data.application', 'SIHANDAL')
            ->where('data.version', 'v1')
            ->etc());
});

test('login issues a sanctum token and returns user identity', function (): void {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id, 'password' => 'secret123']);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'secret123',
        'device_name' => 'test-device',
    ])
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('success', true)
            ->has('data.token')
            ->where('data.token_type', 'Bearer')
            ->where('data.user.email', $user->email)
            ->where('data.user.role', 'opd')
            ->etc());

    expect($user->tokens()->count())->toBe(1);
});

test('login rejects wrong credentials with 422', function (): void {
    $user = User::factory()->create(['password' => 'secret123']);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(422)->assertJson(fn (AssertableJson $json) => $json->where('success', false)->etc());
});

test('login validates required fields', function (): void {
    $this->postJson('/api/v1/auth/login', [])->assertStatus(422)
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('success', false)
            ->where('message', 'Validation failed')
            ->has('errors.email')
            ->has('errors.password')
            ->etc());
});

test('protected endpoints reject unauthenticated requests', function (): void {
    $this->getJson('/api/v1/auth/me')->assertStatus(401)
        ->assertJson(fn (AssertableJson $json) => $json->where('success', false)->where('message', 'Unauthenticated')->etc());
});

test('me returns the authenticated user profile', function (): void {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJson(fn (AssertableJson $json) => $json
            ->where('success', true)
            ->where('data.email', $user->email)
            ->where('data.is_admin', false)
            ->where('data.opd.id', $opd->id)
            ->etc());
});

test('logout revokes the current token', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('device');

    $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    expect($user->fresh()->tokens()->count())->toBe(0);
});

test('refresh issues a new token and revokes the old one', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('device');

    $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
        ->postJson('/api/v1/auth/refresh')
        ->assertOk();

    $newToken = $response->json('data.token');

    expect($newToken)->not->toBeNull()
        ->and($user->fresh()->tokens()->count())->toBe(1);
});

test('api responses never expose password or remember_token', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/auth/me')->json('data');

    expect($response)->not->toHaveKey('password')
        ->and($response)->not->toHaveKey('remember_token');
});
