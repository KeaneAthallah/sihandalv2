<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiEnvelope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class ApiController extends Controller
{
    /**
     * Successful single-resource response in the standard envelope.
     */
    protected function success(mixed $data = null, string $message = 'Data retrieved successfully', int $status = 200): JsonResponse
    {
        return ApiEnvelope::success($data, $message, $status)->toResponse(request());
    }

    /**
     * Successful response with custom meta block.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function withMeta(mixed $data, array $meta, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return ApiEnvelope::withMeta($data, $meta, $message)->toResponse(request());
    }

    /**
     * Business-rule failure response (HTTP 422).
     *
     * @param  array<string, mixed>  $errors
     */
    protected function businessError(string $message, array $errors = []): JsonResponse
    {
        return ApiEnvelope::error($message, $errors, 422)->toResponse(request());
    }

    /**
     * Wraps a paginator into the envelope, transforming each item through the
     * given resource class and mapping Laravel's pagination payload into the
     * flat meta structure the API contract requires.
     *
     * @param  class-string<JsonResource>  $resourceClass
     */
    protected function paginated(LengthAwarePaginator $paginator, string $resourceClass, string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $resourceClass::collection($paginator->items())->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Wraps a resource collection (already resource-transformed) with meta.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function collection(AnonymousResourceCollection $collection, array $meta = [], string $message = 'Data retrieved successfully'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $collection->resolve(),
            'meta' => $meta,
        ]);
    }

    /**
     * Transforms a single model through a resource class and returns it in the envelope.
     */
    protected function resource(JsonResource $resource, string $message = 'Data retrieved successfully', int $status = 200): JsonResponse
    {
        return $this->success($resource->resolve(), $message, $status);
    }

    /**
     * Whitelisted per_page handling: default 20, max 100. When ?all=1 the
     * full (still OPD-scoped) collection is returned instead of a page.
     */
    protected function perPage(Request $request, int $default = 20, int $max = 100): ?int
    {
        if ($request->boolean('all')) {
            return null;
        }

        $perPage = (int) $request->query('per_page', (string) $default);
        if ($perPage < 1) {
            $perPage = $default;
        }

        return min($perPage, $max);
    }

    /**
     * OPD authorization: OPD users may only touch their own OPD. Reuses the
     * same rule as the web base controller (admin bypasses).
     */
    protected function authorizeOpd(Request $request, ?int $opdId): void
    {
        $user = $request->user();

        if ($user !== null && ! $user->isAdmin() && (int) $opdId !== (int) $user->opd_id) {
            abort(403, 'Unauthorized');
        }
    }
}
