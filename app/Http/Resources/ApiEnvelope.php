<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

/**
 * Wraps any payload into the standard SIHANDAL API envelope:
 * { success, message, data, meta } (or { success, message, errors } on failure).
 */
class ApiEnvelope implements Responsable
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        private readonly mixed $data = null,
        private readonly string $message = 'Data retrieved successfully',
        private readonly bool $success = true,
        private readonly int $status = 200,
        private readonly array $meta = [],
    ) {}

    public static function success(mixed $data = null, string $message = 'Data retrieved successfully', int $status = 200): static
    {
        return new static(data: $data, message: $message, success: true, status: $status);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function withMeta(mixed $data, array $meta, string $message = 'Data retrieved successfully'): static
    {
        return new static(data: $data, message: $message, success: true, status: 200, meta: $meta);
    }

    /**
     * @param  array<string, mixed>  $errors  Already keyed by field/group;
     *                                        rendered as the top-level errors object.
     */
    public static function error(string $message, array $errors = [], int $status = 422): static
    {
        return new static(data: null, message: $message, success: false, status: $status, meta: $errors);
    }

    public function toResponse($request): JsonResponse
    {
        $payload = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ($this->success) {
            $payload['data'] = $this->data;
        }

        if ($this->meta !== []) {
            $key = $this->success ? 'meta' : 'errors';
            $payload[$key] = $this->meta;
        }

        return response()->json($payload, $this->status);
    }
}
