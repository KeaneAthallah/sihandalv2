<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(): View
    {
        $kuotaPenerimaanPersen = (float) Setting::get('penerimaan_kuota_persen', 100);

        return view('pengaturan.index', compact('kuotaPenerimaanPersen'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'penerimaan_kuota_persen' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        Setting::set('penerimaan_kuota_persen', (string) $request->input('penerimaan_kuota_persen'));

        return back()->with('success', 'Pengaturan keuangan diperbarui.');
    }
}
