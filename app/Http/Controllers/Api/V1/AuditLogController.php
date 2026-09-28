<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Audit logs are sensitive: route middleware restricts this endpoint to
 * admins. Filters are whitelisted.
 */
class AuditLogController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()
            ->with('user')
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('model'), fn ($q) => $q->where('auditable_type', 'like', '%'.$request->string('model').'%'))
            ->when($request->filled('model_id'), fn ($q) => $q->where('auditable_id', $request->input('model_id')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('tanggal_sampai')))
            ->orderBy('created_at', 'desc');

        return $this->paginated($query->paginate($this->perPage($request)), AuditLogResource::class, 'Data audit log berhasil diambil.');
    }
}
