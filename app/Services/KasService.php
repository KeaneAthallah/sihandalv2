<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SumberDana;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KasService
{
    /**
     * @var array<string, float>
     */
    private array $kuotaPersenCache = [];

    /**
     * Request-scoped memo for ringkasan(). Keyed by
     * "opdId|sumberDanaId|actorRole" — only the actor's
     * admin-ness affects the result (kuota is keyed per
     * sumber dana).
     *
     * @var array<string, array<string, mixed>>
     */
    private array $ringkasanCache = [];

    /**
     * Key Pengaturan untuk kuota penerimaan satu sumber dana.
     */
    public static function kuotaKey(int $sumberDanaId): string
    {
        return "penerimaan_kuota_persen.{$sumberDanaId}";
    }

    /**
     * Kuota persen penerimaan yang boleh dipakai OPD untuk satu
     * sumber dana. Default 100 sehingga perilaku lama tidak berubah
     * sampai admin menurunkannya.
     */
    public function kuotaPersen(int $sumberDanaId): float
    {
        $key = static::kuotaKey($sumberDanaId);

        return $this->kuotaPersenCache[$key] ??= (float) Setting::get($key, 100);
    }

    /**
     * Ringkasan kas untuk satu OPD (dan opsional satu sumber dana).
     *
     * @return array{masuk: float, keluar: float, transfer_net: float, di_commit: float, saldo: float, saldo_efektif: float}
     */
    public function ringkasan(int $opdId, ?int $sumberDanaId = null, ?User $actor = null): array
    {
        $key = $opdId.'|'.($sumberDanaId ?? '-').'|'.($actor === null ? 'guest' : ($actor->isAdmin() ? 'admin' : 'opd'));

        return $this->ringkasanCache[$key] ??= $this->hitungRingkasan($opdId, $sumberDanaId, $actor);
    }

    private function hitungRingkasan(int $opdId, ?int $sumberDanaId = null, ?User $actor = null): array
    {
        $masuk = $this->masuk($opdId, $sumberDanaId);
        $keluar = $this->keluar($opdId, $sumberDanaId);
        $transferNet = $this->transferNet($opdId, $sumberDanaId);
        $diCommit = $this->diCommit($opdId, $sumberDanaId);

        // Kas riil: uang yang benar-benar ada sebelum dikurangi
        // reservasi permintaan dana yang masih menunggu.
        $saldo = round($masuk - $keluar + $transferNet, 2);

        // Kas yang bisa dipakai: kas riil dikurangi reservasi,
        // dengan potongan kuota untuk user OPD.
        $saldoEfektif = round(
            $this->masukSetelahKuota($opdId, $sumberDanaId, $actor) - $keluar + $transferNet - $diCommit,
            2,
        );

        return [
            'masuk' => round($masuk, 2),
            'keluar' => round($keluar, 2),
            'transfer_net' => round($transferNet, 2),
            'di_commit' => round($diCommit, 2),
            'saldo' => $saldo,
            'saldo_efektif' => $saldoEfektif,
        ];
    }

    /**
     * Kas yang bisa dipakai, memperhitungkan kuota untuk user
     * OPD. Admin melihat kas riil tanpa potongan kuota.
     */
    public function saldoEfektif(int $opdId, ?int $sumberDanaId = null, ?User $actor = null): float
    {
        return $this->ringkasan($opdId, $sumberDanaId, $actor)['saldo_efektif'];
    }

    /**
     * Kas masuk setelah kuota penerimaan diterapkan
     * untuk user OPD — angka penerimaan yang boleh
     * dipakai. Admin selalu melihat angka penuh.
     */
    public function masukEfektif(int $opdId, ?int $sumberDanaId = null, ?User $actor = null): float
    {
        return round($this->masukSetelahKuota($opdId, $sumberDanaId, $actor), 2);
    }

    /**
     * Kas riil (tanpa kuota) — kas yang benar-benar ada tanpa
     * dikurangi reservasi.
     */
    public function saldo(int $opdId, ?int $sumberDanaId = null): float
    {
        return $this->ringkasan($opdId, $sumberDanaId)['saldo'];
    }

    /**
     * Kas yang benar-benar bisa dipakai atau dipindahkan tanpa
     * potongan kuota: kas riil dikurangi reservasi permintaan
     * dana. Dipakai untuk validasi transfer dana.
     */
    public function saldoTersedia(int $opdId, ?int $sumberDanaId = null): float
    {
        return round(
            $this->masuk($opdId, $sumberDanaId)
                - $this->keluar($opdId, $sumberDanaId)
                + $this->transferNet($opdId, $sumberDanaId)
                - $this->diCommit($opdId, $sumberDanaId),
            2,
        );
    }

    /**
     * Ringkasan kas per sumber dana untuk satu OPD.
     *
     * Baris dengan sumber_dana_id = 0 adalah
     * bucket "Tanpa Sumber Dana" untuk transaksi
     * legacy yang belum dikaitkan ke sumber dana.
     *
     * @return array<int, array<string, mixed>>
     */
    public function perSumberDana(int $opdId, ?User $actor = null): array
    {
        $rows = SumberDana::query()
            ->orderBy('nama_sumber_dana')
            ->get(['id', 'nama_sumber_dana'])
            ->map(fn ($sumberDana) => [
                'sumber_dana_id' => (int) $sumberDana->id,
                'nama' => (string) $sumberDana->nama_sumber_dana,
                'kuota_persen' => $this->kuotaPersen((int) $sumberDana->id),
                ...$this->ringkasan($opdId, (int) $sumberDana->id, $actor),
            ])
            ->values()
            ->all();

        $legacy = $this->ringkasanTanpaSumberDana($opdId);
        if ($legacy['masuk'] !== 0.0 || $legacy['keluar'] !== 0.0 || $legacy['di_commit'] !== 0.0) {
            $rows[] = [
                'sumber_dana_id' => 0,
                'nama' => 'Tanpa Sumber Dana',
                'kuota_persen' => 100.0,
                ...$legacy,
            ];
        }

        return $rows;
    }

    /**
     * Ringkasan untuk transaksi legacy yang belum
     * dikaitkan ke sumber dana manapun. Transfer
     * dana selalu menyebutkan kedua sumber dana,
     * jadi transfer_net selalu nol di bucket ini.
     * Kuota penerimaan tidak berlaku karena kuota
     * diatur per sumber dana.
     *
     * @return array<string, float>
     */
    private function ringkasanTanpaSumberDana(int $opdId): array
    {
        $masuk = (float) DB::table('transaksi_penerimaans as t')
            ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
            ->where('p.opd_id', $opdId)
            ->whereNull('t.sumber_dana_id')
            ->sum('t.realisasi');

        $keluar = (float) DB::table('pengeluarans')
            ->where('opd_id', $opdId)
            ->whereNull('sumber_dana_id')
            ->sum('jumlah');

        $diCommit = (float) DB::table('kas_sumber_danas')
            ->where('opd_id', $opdId)
            ->whereNull('sumber_dana_id')
            ->sum('di_commit');

        return [
            'masuk' => round($masuk, 2),
            'keluar' => round($keluar, 2),
            'transfer_net' => 0.0,
            'di_commit' => round($diCommit, 2),
            'saldo' => round($masuk - $keluar, 2),
            'saldo_efektif' => round($masuk - $keluar - $diCommit, 2),
        ];
    }

    /**
     * Total kas masuk dari transaksi penerimaan yang dikaitkan
     * ke OPD dan (opsional) sumber dana tertentu.
     */
    private function masuk(int $opdId, ?int $sumberDanaId): float
    {
        return (float) DB::table('transaksi_penerimaans as t')
            ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
            ->where('p.opd_id', $opdId)
            ->when($sumberDanaId !== null, fn ($q) => $q->where('t.sumber_dana_id', $sumberDanaId))
            ->sum('t.realisasi');
    }

    /**
     * Total kas keluar dari pengeluaran yang dicatat untuk OPD
     * dan (opsional) sumber dana tertentu.
     */
    private function keluar(int $opdId, ?int $sumberDanaId): float
    {
        return (float) DB::table('pengeluarans')
            ->where('opd_id', $opdId)
            ->when($sumberDanaId !== null, fn ($q) => $q->where('sumber_dana_id', $sumberDanaId))
            ->sum('jumlah');
    }

    /**
     * Net transfer dana antar sumber dana yang sudah selesai.
     * Untuk satu sumber dana: masuk (sebagai penerima) dikurangi
     * keluar (sebagai pengirim). Untuk total OPD bernilai nol
     * karena perpindahan internal saling meniadakan.
     */
    private function transferNet(int $opdId, ?int $sumberDanaId): float
    {
        if ($sumberDanaId === null) {
            return 0.0;
        }

        $masuk = (float) DB::table('transfer_danas')
            ->where('opd_id', $opdId)
            ->where('sumber_dana_penerima_id', $sumberDanaId)
            ->where('status', 'selesai')
            ->sum('jumlah');

        $keluar = (float) DB::table('transfer_danas')
            ->where('opd_id', $opdId)
            ->where('sumber_dana_pengirim_id', $sumberDanaId)
            ->where('status', 'selesai')
            ->sum('jumlah');

        return $masuk - $keluar;
    }

    /**
     * Total kas yang sudah dijanjikan (di-commit) oleh permintaan
     * dana berstatus menunggu.
     */
    private function diCommit(int $opdId, ?int $sumberDanaId): float
    {
        return (float) DB::table('kas_sumber_danas')
            ->where('opd_id', $opdId)
            ->when($sumberDanaId !== null, fn ($q) => $q->where('sumber_dana_id', $sumberDanaId))
            ->sum('di_commit');
    }

    /**
     * Kas masuk setelah kuota penerimaan diterapkan untuk user OPD.
     *
     * Kuota diatur per sumber dana. Untuk agregat seluruh sumber dana,
     * tiap sumber dana ditimbang dengan kuotanya masing-masing.
     * Transaksi legacy tanpa sumber dana tidak dipotong kuota.
     * Admin selalu melihat angka penuh.
     */
    private function masukSetelahKuota(int $opdId, ?int $sumberDanaId, ?User $actor): float
    {
        if ($actor !== null && $actor->isAdmin()) {
            return $this->masuk($opdId, $sumberDanaId);
        }

        if ($sumberDanaId !== null) {
            return $this->masuk($opdId, $sumberDanaId) * ($this->kuotaPersen($sumberDanaId) / 100);
        }

        return (float) DB::table('transaksi_penerimaans as t')
            ->join('penerimaans as p', 'p.id', '=', 't.penerimaan_id')
            ->where('p.opd_id', $opdId)
            ->selectRaw('t.sumber_dana_id, COALESCE(SUM(t.realisasi), 0) as total')
            ->groupBy('t.sumber_dana_id')
            ->get()
            ->sum(function ($row): float {
                $total = (float) $row->total;

                if ($row->sumber_dana_id === null) {
                    return $total;
                }

                return $total * ($this->kuotaPersen((int) $row->sumber_dana_id) / 100);
            });
    }
}
