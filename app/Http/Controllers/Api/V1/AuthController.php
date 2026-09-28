<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\ApiLoginRequest;
use App\Http\Resources\ApiEnvelope;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * Issue a Sanctum token for a user. Multiple devices are supported by
     * design: each login creates an independent token which can be revoked
     * individually (logout) or in bulk (logout-all via revoke on refresh).
     */
    public function login(ApiLoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email')->lower()->toString())->first();

        if ($user === null || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak cocok dengan catatan kami.'],
            ]);
        }

        $token = $user->createToken(
            $request->input('device_name', 'api'),
        );

        return ApiEnvelope::success(
            data: [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => (new UserResource($user->load('opd')))->resolve(),
            ],
            message: 'Login berhasil.',
        )->toResponse($request);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiEnvelope::success(message: 'Logout berhasil. Token dicabut.')->toResponse(request());
    }

    /**
     * Currently authenticated identity, role and OPD scope.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('opd');

        return $this->success(
            new UserResource($user),
            'Profil pengguna berhasil diambil.',
        );
    }

    /**
     * Refresh the current token: revoke the one in use and issue a new one.
     * This keeps the client's scope identical without ever re-using tokens.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $current = $user->currentAccessToken();
        $name = $current?->name ?? 'api';

        $token = $user->createToken($name);
        $current?->delete();

        return ApiEnvelope::success(
            data: [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => (new UserResource($user->load('opd')))->resolve(),
            ],
            message: 'Token berhasil diperbarui.',
        )->toResponse(request());
    }
}
