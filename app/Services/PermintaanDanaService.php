<?php

namespace App\Services;

use App\Models\Belanja;
use App\Models\KasSumberDana;
use App\Models\Pengeluaran;
use App\Models\PermintaanDana;
use App\Models\Persetujuan;
use App\Models\TahunAnggaran;
use App\Models\User;
use App\Notifications\PermintaanDanaNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Shared PermintaanDana workflow: submit, approve, reject, and
 * the expenditure created from an approved request.
 *
 * Both the web controllers and the API controllers call into this
 * service so the financial rules live in exactly one place.
 *
 * Two ceilings apply at submit time and the smaller one wins:
 *   pagu_tersisa  = belanja.pagu - belanja.realisasi - belanja.dana_di_commit
 *   kas_efektif   = (kas_masuk x kuota%) - kas_keluar + transfer_net - kas_di_commit
 *
 * Pagu is realized on approve; the cash reservation is held from
 * submit until the expenditure is recorded, so the same cash can
 * never be promised to two requests.
 */
class PermintaanDanaService
{
    public function __construct(private readonly DocumentNumberService $numbers) {}

    /**
     * draft -> menunggu, committing pagu and reserving cash on the
     * linked Belanja + (opd, sumber dana) cash pair.
     */
    public function submit(PermintaanDana $permintaanDana, User $actor): PermintaanDana
    {
        $permintaanDana = DB::transaction(function () use ($permintaanDana, $actor) {
            $locked = PermintaanDana::query()
                ->whereKey($permintaanDana->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'draft') {
                throw new RuntimeException('Hanya permintaan draft yang dapat diajukan.');
            }

            $this->commitFunds($locked, $actor);

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
     * menunggu -> disetujui, realizing pagu on the linked Belanja.
     * The cash reservation is intentionally kept until the
     * expenditure is recorded (catatPengeluaran), otherwise the
     * same cash could be promised to a second request in the
     * window between approval and payment.
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
     * menunggu -> ditolak, releasing both the pagu commit and the
     * cash reservation.
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
     * Record the expenditure for an approved request. Every financial
     * field is copied from the request on the server (never trusted
     * from input), so the amount and purpose cannot be tampered with.
     * The cash reservation is released because the promised money is
     * now actually flowing out.
     */
    public function catatPengeluaran(
        PermintaanDana $permintaanDana,
        ?string $noSp2d,
        ?string $tanggalSp2d,
        ?string $tanggal,
    ): Pengeluaran {
        return DB::transaction(function () use ($permintaanDana, $noSp2d, $tanggalSp2d, $tanggal) {
            $locked = PermintaanDana::query()
                ->whereKey($permintaanDana->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'disetujui') {
                throw new RuntimeException('Hanya permintaan dana yang disetujui yang dapat dibuatkan pengeluaran.');
            }

            if ($locked->pengeluaran()->exists()) {
                throw new RuntimeException('Permintaan dana ini sudah memiliki pengeluaran.');
            }

            $pengeluaran = Pengeluaran::create([
                'opd_id' => $locked->opd_id,
                'rekening_id' => $locked->rekening_id,
                'kegiatan_id' => $locked->kegiatan_id,
                'sub_kegiatan_id' => $locked->sub_kegiatan_id,
                'belanja_id' => $locked->belanja_id,
                'sumber_dana_id' => $locked->sumber_dana_id,
                'tahun_anggaran_id' => $locked->tahun_anggaran_id ?? TahunAnggaran::currentActive()?->id,
                'permintaan_dana_id' => $locked->id,
                'sumber_dana' => $locked->sumber_dana ?: $locked->sumberDana?->nama_sumber_dana,
                'jumlah' => $locked->jumlah,
                'keperluan' => $locked->keperluan,
                'no_sp2d' => $noSp2d,
                'tanggal_sp2d' => $tanggalSp2d !== null ? Carbon::parse($tanggalSp2d) : null,
                'tanggal' => $tanggal !== null ? Carbon::parse($tanggal) : now(),
            ]);

            $this->releaseKas($locked);

            return $pengeluaran;
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

    /**
     * Validate the request against both ceilings (pagu and cash) and
     * reserve whichever it passes, taking the smaller available amount.
     */
    protected function commitFunds(PermintaanDana $permintaanDana, User $actor): void
    {
        if (! $permintaanDana->belanja_id) {
            throw new RuntimeException('Belanja wajib dipilih agar permintaan dana terikat pada pagu dan kas.');
        }

        $belanja = Belanja::query()->whereKey($permintaanDana->belanja_id)->lockForUpdate()->first();

        if ($belanja === null) {
            throw new RuntimeException('Belanja terkait tidak ditemukan.');
        }

        if ((int) $belanja->opd_id !== (int) $permintaanDana->opd_id) {
            throw new RuntimeException('Belanja tidak sesuai dengan OPD permintaan.');
        }

        if ((int) $belanja->sumber_dana_id !== (int) $permintaanDana->sumber_dana_id) {
            throw new RuntimeException('Sumber dana permintaan harus sama dengan sumber dana belanja.');
        }

        $jumlah = (float) $permintaanDana->jumlah;

        $paguTersisa = $belanja->availablePagu();
        $kasTersedia = app(KasService::class)->saldoEfektif(
            (int) $permintaanDana->opd_id,
            (int) $permintaanDana->sumber_dana_id,
            $actor,
        );

        if ($jumlah > $paguTersisa) {
            throw new RuntimeException('Jumlah permintaan melebihi pagu belanja yang tersedia.');
        }

        if ($jumlah > $kasTersedia) {
            $kuota = $actor->isAdmin() ? '' : ' (setelah kuota penerimaan)';

            throw new RuntimeException('Jumlah permintaan melebihi kas yang tersedia'.$kuota.'.');
        }

        $belanja->commit($jumlah);
        $this->commitKas($permintaanDana);
    }

    /**
     * Realize pagu on the linked Belanja. The cash reservation is
     * deliberately left in place until the expenditure is recorded.
     */
    protected function realizeFunds(PermintaanDana $permintaanDana): void
    {
        if (! $permintaanDana->belanja_id) {
            return;
        }

        $belanja = Belanja::query()->whereKey($permintaanDana->belanja_id)->lockForUpdate()->first();

        if ($belanja === null) {
            return;
        }

        $belanja->realize((float) $permintaanDana->jumlah);
    }

    /**
     * Release both the pagu commit and the cash reservation, used
     * when a pending request is rejected.
     */
    protected function releaseFunds(PermintaanDana $permintaanDana): void
    {
        if ($permintaanDana->belanja_id) {
            $belanja = Belanja::find($permintaanDana->belanja_id);

            if ($belanja) {
                $belanja->releaseCommit((float) $permintaanDana->jumlah);
            }
        }

        $this->releaseKas($permintaanDana);
    }

    protected function commitKas(PermintaanDana $permintaanDana): void
    {
        if (! $permintaanDana->sumber_dana_id) {
            return;
        }

        KasSumberDana::forPair((int) $permintaanDana->opd_id, (int) $permintaanDana->sumber_dana_id)
            ->commit((float) $permintaanDana->jumlah);
    }

    protected function releaseKas(PermintaanDana $permintaanDana): void
    {
        if (! $permintaanDana->sumber_dana_id) {
            return;
        }

        KasSumberDana::forPair((int) $permintaanDana->opd_id, (int) $permintaanDana->sumber_dana_id)
            ->release((float) $permintaanDana->jumlah);
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
