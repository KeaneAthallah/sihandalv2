<?php

namespace App\Services;

use App\Models\Belanja;
use App\Models\PermintaanDana;
use App\Models\Persetujuan;
use App\Models\User;
use App\Notifications\PermintaanDanaNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Shared PermintaanDana workflow: submit, approve, reject.
 *
 * Both the web controllers and the API controllers call into this service so
 * the financial rules live in exactly one place:
 *   submit  (draft -> menunggu)  locks + re-checks status, commits Belanja funds
 *   approve (menunggu -> disetujui) locks + re-checks, realizes Belanja funds
 *   reject  (menunggu -> ditolak)  locks + re-checks, releases committed funds
 *
 * Row locking, status re-checks, DB transactions and notifications are all
 * preserved exactly as the original controllers implemented them.
 */
class PermintaanDanaService
{
    public function __construct(private readonly DocumentNumberService $numbers) {}

    /**
     * draft -> menunggu, committing funds on the linked Belanja.
     */
    public function submit(PermintaanDana $permintaanDana): PermintaanDana
    {
        $permintaanDana = DB::transaction(function () use ($permintaanDana) {
            $locked = PermintaanDana::query()
                ->whereKey($permintaanDana->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'draft') {
                throw new RuntimeException('Hanya permintaan draft yang dapat diajukan.');
            }

            $this->commitFunds($locked);

            $locked->update([
                'status' => 'menunggu',
                'tanggal' => $locked->tanggal ?? now(),
            ]);

            return $locked->fresh();
        });

        $this->notifyAdmins($permintaanDana);

        return $permintaanDana;
    }

    /**
     * menunggu -> disetujui, realizing funds on the linked Belanja.
     */
    public function approve(PermintaanDana $permintaanDana, User $approver): PermintaanDana
    {
        return DB::transaction(function () use ($permintaanDana, $approver) {
            $locked = PermintaanDana::query()
                ->whereKey($permintaanDana->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'menunggu') {
                throw new RuntimeException('Permintaan ini tidak dalam status menunggu.');
            }

            $this->realizeFunds($locked);

            $locked->update([
                'status' => 'disetujui',
                'tanggal_disetujui' => now(),
            ]);

            Persetujuan::create([
                'permintaan_dana_id' => $locked->id,
                'user_id' => $approver->id,
                'keputusan' => 'disetujui',
                'catatan' => 'Disetujui oleh '.$approver->name,
            ]);

            $this->notifyOpdUsers($locked->fresh(), 'disetujui');

            return $locked->fresh();
        });
    }

    /**
     * menunggu -> ditolak, releasing committed funds on the linked Belanja.
     */
    public function reject(PermintaanDana $permintaanDana, User $rejecter, ?string $catatan = null): PermintaanDana
    {
        return DB::transaction(function () use ($permintaanDana, $rejecter, $catatan) {
            $locked = PermintaanDana::query()
                ->whereKey($permintaanDana->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'menunggu') {
                throw new RuntimeException('Permintaan ini tidak dalam status menunggu.');
            }

            $this->releaseFunds($locked);

            $locked->update([
                'status' => 'ditolak',
            ]);

            Persetujuan::create([
                'permintaan_dana_id' => $locked->id,
                'user_id' => $rejecter->id,
                'keputusan' => 'ditolak',
                'catatan' => $catatan ?? 'Ditolak oleh '.$rejecter->name,
            ]);

            $this->notifyOpdUsers($locked->fresh(), 'ditolak');

            return $locked->fresh();
        });
    }

    /**
     * Race-safe PD-XXXX/YYYY allocation through the shared document counter,
     * seeded with the highest number already issued so counters created after
     * legacy numbering never collide with existing documents.
     */
    public function nextNomorPermintaan(): string
    {
        return $this->numbers->nextSeeded(
            'permintaan_dana',
            'PD',
            (int) now()->format('Y'),
            4,
            fn (int $year) => $this->highestLegacyNumber('PD', $year, 'permintaan_danas', 'nomor_permintaan'),
        );
    }

    /**
     * Race-safe TF-XXXX/YYYY allocation through the shared document counter.
     */
    public function nextNomorTransfer(): string
    {
        return $this->numbers->nextSeeded(
            'transfer_dana',
            'TF',
            (int) now()->format('Y'),
            4,
            fn (int $year) => $this->highestLegacyNumber('TF', $year, 'transfer_danas', 'nomor_transfer'),
        );
    }

    /**
     * Highest previously-issued number for legacy prefix formats (PD-####/YYYY
     * and TF-####/YYYY), used to seed a new document counter.
     */
    private function highestLegacyNumber(string $prefix, int $year, string $table, string $column): int
    {
        $like = $prefix.'-%/'.$year;

        $numbers = DB::table($table)
            ->where($column, 'like', $like)
            ->pluck($column)
            ->map(function (string $nomor) use ($prefix) {
                preg_match('/^'.preg_quote($prefix, '/').'-(\\d+)\\//', $nomor, $matches);

                return $matches[1] !== null ? (int) $matches[1] : null;
            })
            ->filter();

        return $numbers->isEmpty() ? 0 : (int) $numbers->max();
    }

    protected function commitFunds(PermintaanDana $permintaanDana): void
    {
        if (! $permintaanDana->belanja_id) {
            return;
        }

        $belanja = Belanja::query()->whereKey($permintaanDana->belanja_id)->lockForUpdate()->first();

        if ($belanja === null) {
            throw new RuntimeException('Belanja terkait tidak ditemukan.');
        }

        if ($belanja->opd_id !== $permintaanDana->opd_id) {
            throw new RuntimeException('Belanja tidak sesuai dengan OPD permintaan.');
        }

        $jumlah = (float) $permintaanDana->jumlah;

        if ($belanja->availablePagu() < $jumlah) {
            throw new RuntimeException('Jumlah permintaan melebihi pagu belanja yang tersedia.');
        }

        $belanja->commit($jumlah);
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

    protected function notifyAdmins(PermintaanDana $permintaanDana): void
    {
        $admins = User::query()->where('role', 'admin')->get();
        $nomor = $permintaanDana->nomor_permintaan;
        $namaOpd = $permintaanDana->opd->nama ?? 'OPD';

        foreach ($admins as $admin) {
            $admin->notify(new PermintaanDanaNotification(
                $permintaanDana,
                'Permintaan Dana Baru',
                "Permintaan dana {$nomor} dari {$namaOpd} menunggu persetujuan.",
                route('persetujuan.index'),
            ));
        }
    }

    protected function notifyOpdUsers(PermintaanDana $permintaanDana, string $status): void
    {
        $opdUsers = User::query()
            ->where('role', 'opd')
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
