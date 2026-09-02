<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\PermintaanDana;
use App\Models\Persetujuan;
use App\Models\User;
use App\Notifications\PermintaanDanaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersetujuanController extends Controller
{
    public function index(Request $request)
    {
        $permintaanDanas = $this->applyOpdScope(PermintaanDana::with(['opd', 'persetujuans', 'sumberDana']), $request->user())
            ->where('status', 'menunggu')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalMenunggu = $permintaanDanas->count();
        $totalMenungguNilai = $permintaanDanas->sum('jumlah');

        return view('persetujuan.index', compact(
            'permintaanDanas', 'totalMenunggu', 'totalMenungguNilai'
        ));
    }

    public function setujui(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());

        if (! request()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menyetujui permintaan dana.');
        }

        DB::transaction(function () use ($permintaanDana) {
            $permintaanDana = PermintaanDana::findOrFail($permintaanDana->id);

            if ($permintaanDana->status !== 'menunggu') {
                throw new \RuntimeException('Permintaan ini tidak dalam status menunggu.');
            }

            $this->realizeFunds($permintaanDana);

            $permintaanDana->update([
                'status' => 'disetujui',
                'tanggal_disetujui' => now(),
            ]);

            Persetujuan::create([
                'permintaan_dana_id' => $permintaanDana->id,
                'user_id' => auth()->id(),
                'keputusan' => 'disetujui',
                'catatan' => 'Disetujui oleh '.auth()->user()->name,
            ]);

            $this->notifyOpdUser($permintaanDana->fresh(), 'disetujui');
        });

        return back()->with('success', 'Permintaan dana berhasil disetujui.');
    }

    public function tolak(PermintaanDana $permintaanDana)
    {
        $this->authorizeOpdRecord($permintaanDana, request()->user());

        if (! request()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang dapat menolak permintaan dana.');
        }

        DB::transaction(function () use ($permintaanDana) {
            $permintaanDana = PermintaanDana::findOrFail($permintaanDana->id);

            if ($permintaanDana->status !== 'menunggu') {
                throw new \RuntimeException('Permintaan ini tidak dalam status menunggu.');
            }

            $this->releaseFunds($permintaanDana);

            $permintaanDana->update([
                'status' => 'ditolak',
            ]);

            Persetujuan::create([
                'permintaan_dana_id' => $permintaanDana->id,
                'user_id' => auth()->id(),
                'keputusan' => 'ditolak',
                'catatan' => 'Ditolak oleh '.auth()->user()->name,
            ]);

            $this->notifyOpdUser($permintaanDana->fresh(), 'ditolak');
        });

        return back()->with('success', 'Permintaan dana ditolak.');
    }

    protected function realizeFunds(PermintaanDana $permintaanDana): void
    {
        if ($permintaanDana->belanja_id) {
            $belanja = Belanja::find($permintaanDana->belanja_id);
            if ($belanja) {
                $belanja->realize((float) $permintaanDana->jumlah);
            }
        }
    }

    protected function releaseFunds(PermintaanDana $permintaanDana): void
    {
        if ($permintaanDana->belanja_id) {
            $belanja = Belanja::find($permintaanDana->belanja_id);
            if ($belanja) {
                $belanja->releaseCommit((float) $permintaanDana->jumlah);
            }
        }
    }

    protected function notifyOpdUser(PermintaanDana $permintaanDana, string $status): void
    {
        $opdUsers = User::where('role', 'opd')
            ->where('opd_id', $permintaanDana->opd_id)
            ->get();

        $title = $status === 'disetujui' ? 'Permintaan Dana Disetujui' : 'Permintaan Dana Ditolak';
        $message = "Permintaan dana {$permintaanDana->nomor_permintaan} telah {$status}.";

        foreach ($opdUsers as $user) {
            $user->notify(new PermintaanDanaNotification(
                $permintaanDana,
                $title,
                $message,
                route('permintaan-dana.index'),
            ));
        }
    }
}
