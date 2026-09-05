# SIHANDAL V2

**Sistem Informasi Keuangan Daerah** is a regional financial information system for Indonesian local / regional government agencies (OPD — *Organisasi Perangkat Daerah*).

Each agency (OPD) tracks budgets (pagu), revenue (penerimaan), expenditure (pengeluaran), cash positions (posisi kas), programs/activities (program-kegiatan), and submits formal fund requests (permintaan dana) that flow through an approval workflow (`draft → menunggu → disetujui/ditolak`) managed by admins.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Data Model Overview](#data-model-overview)
- [Architecture Patterns](#architecture-patterns)
- [Installation](#installation)
- [Common Tasks](#common-tasks)
- [Testing](#testing)
- [License](#license)

---

## Features

| Module | Purpose |
| ------ | ------- |
| Dashboard | Overview: bar/donut/radial charts, stat cards, recent fund requests, status breakdown |
| OPD Management | List departments, drill into OPD detail (Dinas / Unit / UPT cards + full hierarchy table) |
| UPT | Technical implementation units (OPD → UPT(s)) CRUD |
| Sumber Dana | Master fund sources used across the hierarchy |
| Rekening Kas | Master cash/rekening accounts (`kas` / `pendapatan` / `belanja`); balance is derived from transactions, never stored |
| Program & Kegiatan | Program + nested Kegiatan budget tracking with codes |
| Sub Kegiatan | Nested under Kegiatan (Program → Kegiatan → SubKegiatan) |
| Belanja | Budget leaf (rekening + sumber dana + pagu); owns fund locking (commit/release/realize) |
| Penerimaan | Master data Penerimaan (definition: OPD, rekening, sumber dana, tahun anggaran, target) + Transaksi Penerimaan (realisasi/tanggal/keterangan per transaction), with computed realization accessors. A master's rekening **must** be tipe `pendapatan` (server-side validated) |
| Pengeluaran | Expenditure with budget vs. actual + FK kegiatan/sub/belanja. The rekening **must** be tipe `belanja` (server-side validated) |
| Posisi Kas | Cash position snapshot (`saldo_awal` ± changes) |
| Permintaan Dana | Fund-request workflow, links to Kegiatan/SubKegiatan/Belanja |
| Persetujuan | Admin approve/reject of pending requests (realize/release) |
| Transfer Dana | Fund-transfer tracking with status workflow |
| Tahun Anggaran | Fiscal-year management (only one active at a time) |
| Laporan | Read-only reports: penerimaan, pengeluaran, posisi kas, rekap permintaan — each with CSV export |
| Notifikasi | In-app (database) notifications on request flow |
| Audit Log | Automatic trail of create/update/delete on models using the `Auditable` trait |
| User Management | Admin-only user CRUD + role/OPD assignment |
| Data Import | CLI command to import the full budget (CSV) and revenue (XLSX) datasets into the normalized hierarchy with reconciliation |

### Rekening business rules

`rekenings.tipe` is one of `kas` | `pendapatan` | `belanja`, and it is enforced **server-side**:

- **Penerimaan** may only reference a Rekening with `tipe = 'pendapatan'`.
- **Pengeluaran** may only reference a Rekening with `tipe = 'belanja'`.
- `kas` Rekening is accepted by neither module.

Validation lives in the `Store*`/`Update*` Form Requests (`Rule::exists('rekenings', 'id')->where(tipe)`, Indonesian error messages) on both **create and update**, with the dropdowns filtered to matching types as a UX convenience.

---

## Tech Stack

| Layer | Technology | Version |
| ----- | ---------- | ------- |
| Language | PHP | 8.5 |
| Framework | Laravel | 13.x |
| Auth | Laravel Breeze | 2.x |
| Database | MySQL | — |
| CSS | Tailwind CSS | 4.x (via `@tailwindcss/vite`) |
| JS | Alpine.js | 3.x |
| Charts | ApexCharts | 5.x |
| Build | Vite | — |
| Testing | Pest / PHPUnit | 4.x / 12 |

---

## Data Model Overview

Budget is aggregated upward from the leaves:

```
Program → Kegiatan → SubKegiatan → Belanja (rekening + sumber dana + pagu)
```

with pagu entered only at the **Belanja leaf** and derived upward through model helper methods. Master data (Sumber Dana, Rekening, Program, Kegiatan, Sub Kegiatan) is referenced by foreign keys; legacy denormalized text columns are kept for backfill/display only.

Key money logic:

- **Belanja** — `availablePagu()` = pagu − realisasi − `dana_di_commit`; `commit()`, `releaseCommit()`, `realize()` encapsulate fund-locking invariants and throw when a request exceeds available budget.
- **Penerimaan** — realization (realisasi) is computed as the sum of its `transaksi_penerimaans`; `persentase` and `tanggal` are eager-load-aware accessors.
- **Rekening** — no persisted `saldo`; balances are derived via `totalPenerimaan()`, `totalPengeluaran()`, and `saldo()` from transactions, DB-aggregated and OPD-scoped.

---

## Architecture Patterns

1. **Standard MVC** — form-request validation → controller → Eloquent model → Blade view. Server-rendered pages enhanced with Alpine.js (no separate frontend framework).
2. **Fund locking on Belanja** — domain methods (`commit`/`releaseCommit`/`realize`) encapsulate money invariants.
3. **Transactional multi-step operations** — submit/approve/reject and the CSV importer run inside `DB::transaction`.
4. **Auto-generated identifiers** — sequential `PD-XXXX/YYYY` and `TF-XXXX/YYYY` strings per year.
5. **Computed fields** — `persentase`/`saldo_akhir` persisted on write; revenue realization and rekening balance computed on read from transactions (single source of truth).
6. **Shared validation trait** — `ValidatesPermintaanDana` used by Store/Update requests for identical business rules.
7. **Automatic audit trail** — `Auditable` trait writes diff snapshots to `audit_logs`.
8. **In-app notifications** — database-channel notifications on the fund-request flow.
9. **Separate read/report controllers** — each money module has a read-only `Laporan*`/`Rekap*` controller with streaming CSV export.
10. **Reusable Blade component library** (~23 components) backed by a `@layer components` design system.
11. **OPD-scoped multi-tenancy** — base `Controller` helpers (`applyOpdScope`, `userOpds`, `authorizeOpdRecord`) confine OPD users to their own rows.
12. **Server-side business rules** (rekening tipe, cross-OPD FK checks) — enforced in Form Requests, not just the UI.

---

## Installation

Prerequisites: PHP 8.5, Composer, Node.js/npm, MySQL.

```bash
# 1. Install PHP + JS dependencies
composer install
npm install

# 2. Environment configuration
cp .env.example .env
php artisan key:generate
#   → edit .env: set DB_* credentials for your MySQL database

# 3. Run migrations and seed (seeds run the importer idempotently)
php artisan migrate --seed

# 4. Build frontend assets (or use npm run dev during development)
npm run build

# 5. Start the development server
php artisan serve
```

### Importing the full budget + revenue dataset

```bash
php artisan app:import-sihandal database/seeders/data/sumberdana26.csv \
                              database/seeders/data/penerimaan-2026.xlsx
#   + --dry-run   (validate only, persists nothing)
#   + --fresh     (wipe + reimport)
#   + --force     (skip confirmation)
```

---

## Common Tasks

- Reset sample data: `php artisan db:seed --class=SampleDataSeeder`
- Rebuild frontend: `npm run build` (or `npm run dev` / `composer run dev`)
- Inspect routes: `php artisan route:list`
- Clear views: `php artisan view:clear` (after editing Blade)

---

## Testing

```bash
php artisan test --compact                          # full suite
php artisan test --compact --filter=PageSmokeTest    # page-render smoke
vendor/bin/pint --dirty --format agent               # code style
```

Key test files: `ModuleWorkflowTest`, `OpdScopingTest`, `RekeningTipeValidationTest`, `HierarchyTest`, `PermintaanDanaCommitTest`, `PageSmokeTest`, `UserManagementTest`, `SihandalImportTest`, `ProfileTest`, `Auth/`.

---

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). This project builds upon it for a regional government financial management application.