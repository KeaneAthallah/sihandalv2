<?php

namespace App\Imports;

use App\Models\Opd;
use App\Models\Rekening;
use App\Models\SumberDana;
use App\Models\TahunAnggaran;
use Illuminate\Support\Collection;

/**
 * Shared helpers for master-data imports: header/cell
 * normalization, typed field readers, and lookup resolvers
 * (by kode, then nama) backed by a per-import cache.
 */
abstract class MasterImportDefinition implements ImportDefinition
{
    private ?Collection $opds = null;

    private ?Collection $rekenings = null;

    private ?Collection $sumberDanas = null;

    private ?Collection $tahunAnggarans = null;

    /**
     * Map a workbook header row onto the canonical headers,
     * matching case- and punctuation-insensitively.
     *
     * @param  list<string|null>  $sheetHeader
     * @return array<string, int> canonical header => column index
     */
    public function headerMap(array $sheetHeader): array
    {
        $normalized = array_map(
            fn ($header) => $header === null ? '' : $this->normalizeHeader((string) $header),
            $sheetHeader
        );

        $map = [];
        foreach ($this->headers() as $header) {
            $index = array_search($this->normalizeHeader($header), $normalized, true);

            if ($index !== false) {
                $map[$header] = $index;
            }
        }

        return $map;
    }

    /**
     * Normalize a sheet cell into a trimmed string, or null when empty.
     */
    protected function value(array $row, string $header): ?string
    {
        $value = $row[$header] ?? null;

        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        $value = (string) $value;
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    protected function requiredString(array $row, string $header, string $label): string
    {
        $value = $this->value($row, $header);

        if ($value === null || $value === '') {
            $this->fail("Kolom {$label} wajib diisi.");
        }

        return $value;
    }

    protected function optionalString(array $row, string $header): ?string
    {
        $value = $this->value($row, $header);

        return $value === '' ? null : $value;
    }

    protected function requiredNumeric(array $row, string $header, string $label): float
    {
        $value = $this->value($row, $header);

        if ($value === null || $value === '' || ! is_numeric($value)) {
            $this->fail("Kolom {$label} wajib berupa angka.");
        }

        return (float) $value;
    }

    protected function optionalNumeric(array $row, string $header, float $default = 0.0): float
    {
        $value = $this->value($row, $header);

        if ($value === null || $value === '') {
            return $default;
        }

        if (! is_numeric($value)) {
            $this->fail("Kolom {$header} harus berupa angka.");
        }

        return (float) $value;
    }

    protected function fail(string $message): never
    {
        throw new ImportRowException($message);
    }

    protected function resolveOpd(string $value): int
    {
        $needle = strtolower($value);

        foreach ($this->opdList() as $opd) {
            if (strtolower((string) $opd->kode) === $needle) {
                return (int) $opd->id;
            }
        }

        foreach ($this->opdList() as $opd) {
            if (strtolower((string) $opd->nama) === $needle) {
                return (int) $opd->id;
            }
        }

        $this->fail("OPD '{$value}' tidak ditemukan.");
    }

    protected function resolveRekeningByKode(?string $kode): ?int
    {
        if ($kode === null) {
            return null;
        }

        $needle = strtolower($kode);

        foreach ($this->rekeningList() as $rekening) {
            if (strtolower((string) $rekening->kode) === $needle) {
                return (int) $rekening->id;
            }
        }

        $this->fail("Rekening dengan kode '{$kode}' tidak ditemukan.");
    }

    /**
     * Resolve a rekening by kode, falling back to nama.
     */
    protected function resolveRekening(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $needle = strtolower($value);

        foreach ($this->rekeningList() as $rekening) {
            if (strtolower((string) $rekening->kode) === $needle) {
                return (int) $rekening->id;
            }
        }

        foreach ($this->rekeningList() as $rekening) {
            if (strtolower((string) $rekening->nama) === $needle) {
                return (int) $rekening->id;
            }
        }

        $this->fail("Rekening '{$value}' tidak ditemukan.");
    }

    protected function resolveSumberDana(?string $nama): ?int
    {
        if ($nama === null) {
            return null;
        }

        $needle = strtolower($nama);

        foreach ($this->sumberDanaList() as $sumberDana) {
            if (strtolower((string) $sumberDana->nama_sumber_dana) === $needle) {
                return (int) $sumberDana->id;
            }
        }

        $this->fail("Sumber dana '{$nama}' tidak ditemukan.");
    }

    protected function resolveTahun(?string $tahun): ?int
    {
        if ($tahun === null) {
            return null;
        }

        foreach ($this->tahunAnggaranList() as $tahunAnggaran) {
            if ((string) $tahunAnggaran->tahun === $tahun) {
                return (int) $tahunAnggaran->id;
            }
        }

        $this->fail("Tahun anggaran '{$tahun}' tidak ditemukan.");
    }

    protected function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;

        return strtolower((string) preg_replace('/[^a-z0-9]/u', '', $header));
    }

    private function opdList(): Collection
    {
        return $this->opds ??= Opd::orderBy('nama')->get(['id', 'kode', 'nama']);
    }

    private function rekeningList(): Collection
    {
        return $this->rekenings ??= Rekening::orderBy('kode')->get(['id', 'kode', 'nama', 'tipe', 'parent_id']);
    }

    private function sumberDanaList(): Collection
    {
        return $this->sumberDanas ??= SumberDana::orderBy('nama_sumber_dana')->get(['id', 'nama_sumber_dana']);
    }

    private function tahunAnggaranList(): Collection
    {
        return $this->tahunAnggarans ??= TahunAnggaran::orderByDesc('tahun')->get(['id', 'tahun']);
    }
}
