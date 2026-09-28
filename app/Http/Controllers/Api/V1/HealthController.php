<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        $database = 'connected';

        try {
            DB::select('select 1');
        } catch (\Throwable) {
            $database = 'unavailable';
        }

        return $this->success([
            'status' => 'ok',
            'application' => 'SIHANDAL',
            'version' => 'v1',
            'database' => $database,
        ]);
    }
}
