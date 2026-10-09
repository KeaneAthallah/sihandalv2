<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Setting;
use App\Models\SumberDana;
use App\Services\KasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends ApiController
{
    /**
     * Kuota penerimaan per sumber dana (admin).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        return $this->success($this->payload());
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'kuota' => ['required', 'array'],
            'kuota.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $sumberDanaIds = SumberDana::query()->pluck('id')->map(fn ($id): int => (int) $id)->all();

        foreach ($validated['kuota'] as $sumberDanaId => $persen) {
            if (! in_array((int) $sumberDanaId, $sumberDanaIds, true)) {
                continue;
            }

            Setting::set(KasService::kuotaKey((int) $sumberDanaId), (string) $persen);
        }

        return $this->success($this->payload(), 'Pengaturan diperbarui.');
    }

    /**
     * @return array{kuota_penerimaan: array<int, array{sumber_dana_id: int, nama_sumber_dana: string, persen: float}>}
     */
    private function payload(): array
    {
        $sumberDanas = SumberDana::query()->orderBy('nama_sumber_dana')->get(['id', 'nama_sumber_dana']);

        return [
            'kuota_penerimaan' => $sumberDanas->map(fn (SumberDana $sumberDana): array => [
                'sumber_dana_id' => (int) $sumberDana->id,
                'nama_sumber_dana' => (string) $sumberDana->nama_sumber_dana,
                'persen' => (float) Setting::get(KasService::kuotaKey((int) $sumberDana->id), 100),
            ])->all(),
        ];
    }

    private function authorizeAdmin(Request $request): void
    {
        $user = $request->user();

        if ($user === null || ! $user->isAdmin()) {
            abort(403, 'Unauthorized');
        }
    }
}
