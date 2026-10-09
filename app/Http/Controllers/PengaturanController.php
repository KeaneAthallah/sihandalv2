<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\SumberDana;
use App\Services\KasService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(): View
    {
        $sumberDanas = SumberDana::query()->orderBy('nama_sumber_dana')->get(['id', 'nama_sumber_dana']);

        $kuota = $sumberDanas->mapWithKeys(fn (SumberDana $sumberDana): array => [
            (int) $sumberDana->id => (float) Setting::get(KasService::kuotaKey((int) $sumberDana->id), 100),
        ]);

        return view('pengaturan.index', compact('sumberDanas', 'kuota'));
    }

    public function update(Request $request): RedirectResponse
    {
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

        return back()->with('success', 'Kuota penerimaan per sumber dana diperbarui.');
    }
}
