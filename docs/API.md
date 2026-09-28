# SIHANDAL V2 — REST API Documentation (v1)

SIHANDAL V2 (Sistem Informasi Keuangan Daerah) exposes a versioned REST API under `/api/v1`. The API is an **additional interface** to the same SIHANDAL domain used by the Blade web application: it reuses the existing models, form-request validation rules, domain methods (`Belanja::commit()`, `realize()`, …), services, financial invariants, transactions and authorization logic. No business logic is duplicated in API controllers.

- **Interactive documentation (Stoplight Elements):** `/api/documentation` (aliases Scramble's `/docs/api`)
- **OpenAPI 3 schema:** `/api/openapi.json` (aliases `/docs/api.json`)
- **Postman collection:** `docs/SIHANDAL-V1.postman_collection.json`

---

## 1. Architecture

```
API Request
  → API Form Request  (app/Http/Requests/Api/*  — same rules as web requests)
  → API Controller    (app/Http/Controllers/Api/V1/* — thin, no business logic)
  → Domain            (existing Models / Services / Traits / DB transactions)
  → API Resource      (app/Http/Resources/* — whitelisted fields only)
  → JSON envelope
```

Key layers introduced for the API:

| Layer | Location | Purpose |
|---|---|---|
| Envelope | `app/Http/Resources/ApiEnvelope.php` | `{success, message, data, meta}` / `{success, message, errors}` |
| Base controller | `app/Http/Controllers/Api/ApiController.php` | envelope helpers, pagination meta, OPD authorization, per-page cap |
| Shared workflows | `app/Services/PermintaanDanaService.php` | submit/approve/reject used by **both** web and API |
| Shared numbers | `app/Services/DocumentNumberService.php` | race-safe REG/PD/TF numbering (extended with seeded counters) |
| Aggregates | `app/Services/FinancialSummaryService.php` | SQL-aggregate dashboard/AI/report totals |
| Exception mapping | `bootstrap/app.php` | Validation→422, Auth→401, Forbidden→403, NotFound→404, business rule→422, unexpected→500 |

---

## 2. Authentication (Laravel Sanctum)

Tokens are bearer tokens. Each login creates an independent token (multi-device); revoking one does not affect others.

| Endpoint | Method | Description |
|---|---|---|
| `/api/v1/auth/login` | POST | Issue token. Rate limit: 5/min per IP. Body: `email`, `password`, `device_name?` |
| `/api/v1/auth/logout` | POST | Revoke current token |
| `/api/v1/auth/me` | GET | Current user identity, role, OPD |
| `/api/v1/auth/refresh` | POST | Revoke current token and issue a new one |

```bash
curl -X POST https://host/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"secret","device_name":"android"}'
```

Response:

```json
{
  "success": true,
  "message": "Login berhasil.",
  "data": {
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer",
    "user": { "id": 1, "name": "...", "email": "...", "role": "opd", "is_admin": false, "opd": {...} }
  }
}
```

Send subsequent requests with `Authorization: Bearer <token>`. `password` and `remember_token` are never serialized.

---

## 3. Standard response format

Single resource:

```json
{ "success": true, "message": "Data retrieved successfully", "data": { }, "meta": { } }
```

Collection (paginated):

```json
{
  "success": true, "message": "...",
  "data": [ ],
  "meta": { "current_page": 1, "per_page": 20, "total": 100, "last_page": 5 }
}
```

Validation error (422):

```json
{ "success": false, "message": "Validation failed", "errors": { "field": ["The field is required."] } }
```

Business-rule failure (422) — e.g. commit beyond `availablePagu()`:

```json
{ "success": false, "message": "Unable to perform operation", "errors": { "business": ["Dana commit melebihi pagu yang tersedia."] } }
```

Other statuses: `401 {"success":false,"message":"Unauthenticated"}`, `403 {"success":false,"message":"Unauthorized"}`, `404 {"success":false,"message":"Resource not found"}`, `429 {"success":false,"message":"Too many requests"}`, `500 {"success":false,"message":"Internal server error"}` (production; internals are never exposed).

---

## 4. Pagination, filtering, sorting

- `?page=1&per_page=20` — default 20, **max 100**. Add `?all=1` for an unpaginated (still OPD-scoped) list on endpoints that support it.
- `?search=` — whitelisted searchable fields per module (e.g. `nama`, `kode`, `nomor_registrasi`).
- Date ranges use `tanggal_dari` / `tanggal_sampai` (matching the existing web controllers).
- Module filters: `opd_id`, `tahun_anggaran_id`, `sumber_dana_id`, `rekening_id`, `kegiatan_id`, `sub_kegiatan_id`, `belanja_id`, `status`, `penerimaan_id`, `tipe`.
- Sorting is fixed per module (no arbitrary `orderBy` from client input). All filter/sort names are whitelisted; raw SQL clauses are never built from request input.

---

## 5. Authorization & OPD multi-tenancy

- Roles: `admin` (province-wide) and `opd` (scoped to `users.opd_id`).
- Every list/detail/write endpoint applies the existing OPD scoping rule (`userOpds()` / `applyOpdScope()` semantics). OPD users can only see and modify their own OPD's records; cross-OPD access returns 403.
- `RekeningBank` is **intentionally global** (no OPD scoping), matching the web behavior.
- Admin-only modules: rekening kas writes, tahun anggaran writes, audit logs, approve/reject permintaan dana.
- Penerimaan masters with `opd_id = null` (province-wide) are admin-managed only.

---

## 6. Endpoint inventory

Complete inventory with request/response examples: see `docs/API_ENDPOINTS.md` and the live OpenAPI document.

### Authentication
`POST /api/v1/auth/login` · `POST /api/v1/auth/logout` · `GET /api/v1/auth/me` · `POST /api/v1/auth/refresh`

### Health
`GET /api/v1/health` — public.

### Master data
- `GET /api/v1/opds`, `GET /api/v1/opds/{id}` (read-only, scoped)
- `programs`, `kegiatan`, `sub-kegiatan` — full CRUD (hierarchy guards: pagu lives at Belanja leaf; program/kegiatan/sub-kegiatan deletion blocked when funds are committed/realized)
- `rekenings` — kas/pendapatan/belanja master; hierarchy validated (kas parent/child, cycle guard, delete guard); writes admin-only
- `sumber-dana` — CRUD
- `rekening-banks` — global master CRUD + `GET /api/v1/rekening-banks-active`; banks referenced by BKU rows cannot be deleted (deactivate instead); inactive banks cannot be used in transactions
- `tahun-anggaran` — list/create/update + `POST /api/v1/tahun-anggaran/{id}/activate` and `GET /api/v1/tahun-anggaran-active`; only one active year, writes admin-only, activation audited

### Budget & financial operations
- `GET|POST /api/v1/belanja`, `GET|PUT /api/v1/belanja/{id}` — no DELETE for protected rows; update cannot reduce historical `realisasi` nor set `pagu` below `commit + realisasi`
- `POST /api/v1/belanja/{id}/commit` — `{ "amount": 100000 }` → available pagu → `dana_di_commit` (invariant `realisasi + commit <= pagu` enforced by the domain)
- `POST /api/v1/belanja/{id}/release` — `dana_di_commit` → available (never below zero)
- `POST /api/v1/belanja/{id}/realize` — records realization, consumes commit first, cannot exceed `availablePagu()`

### Revenue
- `penerimaan` CRUD — realization is **computed** from `SUM(transaksi_penerimaans.realisasi)`; never persisted on the master; resource exposes `target`, `realisasi`, `persentase`
- `GET /api/v1/penerimaan-details` (read-only; details are managed via the parent save, same as web)
- `transaksi-penerimaan` CRUD — `nomor_registrasi` is server-generated (`REG-XXXXX/YYYY`, race-safe) and immutable; nested `bkus` validated (sum rule, active bank)
- Nested BKU: `POST /api/v1/transaksi-penerimaan/{id}/bkus`, `PATCH …/bkus/{bku}`, `DELETE …/bkus/{bku}` — every write re-checks `SUM(BKU) == realisasi`

### Expenditure & cash
- `pengeluaran` CRUD — hierarchy consistency enforced (kegiatan → sub kegiatan → belanja must belong to the selected OPD; rekening must be tipe `belanja`); `persentase` computed server-side
- `posisi-kas` list/create/show/update — only a **kas leaf** rekening is selectable; `saldo_akhir` computed server-side

### Fund request workflow
- `GET|POST /api/v1/permintaan-dana`, `GET|PUT|PATCH /api/v1/permintaan-dana/{id}` — status is never client-settable; edits only in `draft`/`ditolak`
- `POST /api/v1/permintaan-dana/{id}/submit` — draft → menunggu, commits Belanja funds (row-locked, transactional)
- `POST /api/v1/permintaan-dana/{id}/approve` — admin-only; menunggu → disetujui, realizes funds, records `Persetujuan`
- `POST /api/v1/permintaan-dana/{id}/reject` — admin-only; menunggu → ditolak, releases committed funds, records rejection
- `GET /api/v1/permintaan-dana/{id}/persetujuan` — approval history

### Transfers
`transfer-dana` CRUD — `TF-XXXX/YYYY` auto-generated race-safe; status starts `draft`; `selesai` is final (no edit/delete); `tanggal_selesai` set server-side.

### Dashboard / Reports / AI
- `GET /api/v1/dashboard` (+ `/summary`, `/budget`, `/revenue`, `/expenditure`, `/cash`, `/programs`, `/activity`) — SQL aggregates only
- `GET /api/v1/reports/{penerimaan|pengeluaran|posisi-kas|permintaan-dana}` — paginated rows + summary totals in `meta`
- `GET /api/v1/reports/…/export` — streamed CSV
- `GET /api/v1/ai/{summary|budget-summary|revenue-summary|expenditure-summary|cash-summary|program-summary|financial-alerts}` — structured factual reads for AI consumers, same auth/OPD rules

### Notifications & Audit
- `GET /api/v1/notifications`, `GET /api/v1/notifications/unread`, `POST /api/v1/notifications/{id}/read`, `POST /api/v1/notifications/read-all` — strictly per-user
- `GET /api/v1/audit-logs` — admin-only, whitelisted filters (`user_id`, `action`, `model`, `model_id`, `tanggal_dari/sampai`)

---

## 7. Financial invariants preserved

1. Pagu lives only at the Belanja leaf; program/kegiatan/sub-kegiatan totals are derived.
2. `availablePagu() = pagu - realisasi - dana_di_commit`.
3. `realisasi + dana_di_commit <= pagu` (commit & realize reject otherwise).
4. Commit moves available → `dana_di_commit`; release moves back (never below 0).
5. Realize cannot exceed available pagu and consumes commit first.
6. Historical realization cannot be reduced through updates.
7. Penerimaan realization is computed from transaction SUM; never a column.
8. BKU rows must sum exactly to the transaction `realisasi` (create, update, nested writes).
9. Inactive RekeningBank cannot be booked; used banks cannot be deleted.
10. `nomor_registrasi`, `nomor_permintaan`, `nomor_transfer` are server-generated, race-safe, immutable.
11. Workflow transitions are explicit endpoints; generic PATCH cannot set `status = disetujui`.
12. Approval realizes, rejection releases, submission commits — all in `DB::transaction` with `lockForUpdate`.
13. OPD scoping on every endpoint; audit logging continues for API writes (Auditable trait).

---

## 8. Rate limiting

| Limiter | Applied to | Limit |
|---|---|---|
| `api-auth` | login | 5/min per IP |
| `api-financial` | submit/approve/reject workflow actions | 10/min per user |
| `api-read` | AI summary endpoints | 120/min per user |

---

## 9. Development

```bash
composer install
php artisan migrate          # includes Sanctum's personal_access_tokens table
php artisan serve
npm run dev                  # or: npm run build
```

Run the API tests:

```bash
php artisan test --compact tests/Feature/Api
```

Generate the static OpenAPI file:

```bash
php artisan scramble:export  # writes api.json
```

---

## 10. Testing & deployment notes

- The API test suite covers authentication, OPD scoping, CRUD, validation, pagination/filtering, financial invariants (commit/release/realize), BKU rules, workflow transitions, reports, notifications and audit logging.
- Existing web tests are untouched and must continue to pass; run `php artisan test --compact` for the full suite.
- Deploy as usual; ensure `personal_access_tokens` migrations run. Sanctum tokens are hashed (`hash` option in `config/sanctum.php`).
- API versioning: everything lives under `/api/v1`; v2 can be introduced later by adding `routes/api.php` groups and new controller namespaces without touching v1.
