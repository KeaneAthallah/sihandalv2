<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    /**
     * Application settings (admin). Currently holds the
     * revenue quota percentage applied to OPD users.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        return $this->success([
            'penerimaan_kuota_pesen' => (float) Setting::get('penerimaan_kuota_pesen', 100),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $request->validate([
            'penerimaan_kuota_pesen' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::set('penerimaan_kuota_pesen', (string) $request->input('penerimaan_kuota_pesen'));

        return $this->success([
            'penerimaan_kuota_pesen' => (float) Setting::get('penerimaan_kuota_pesen', 100),
        ], 'Pengaturan diperbarui.');
    }

    private function authorizeAdmin(Request $request): void
    {
        $user = $request->user();

        if ($user === null || ! $user->isAdmin()) {
            abort(403, 'Unauthorized');
        }
    }
}
