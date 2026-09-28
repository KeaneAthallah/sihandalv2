# SIHANDAL API v1 — Complete Endpoint Inventory

Base URL: `/api/v1` — Authentication: `Authorization: Bearer <token>` (Sanctum).
All list endpoints support `?page`, `?per_page` (default 20, max 100), module filters, and `?search` where noted. `?all=1` returns an unpaginated (still OPD-scoped) collection.

Legend: 🔓 public · 🔒 authenticated · 👑 admin-only for writes/action · 💰 financial workflow (rate-limited, transactional, row-locked)

## Authentication
| Method | URI | Access | Notes |
|---|---|---|---|
| POST | /api/v1/auth/login | 🔓 | 5/min per IP. Body: `email`, `password`, `device_name?` → `data.token` |
| POST | /api/v1/auth/logout | 🔒 | Revokes current token |
| GET | /api/v1/auth/me | 🔒 | User identity, role, OPD |
| POST | /api/v1/auth/refresh | 🔒 | Revokes current token, issues new one |

## System
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/health | 🔓 | App status + DB connectivity |
| GET | /api/documentation | 🔓 (local) | Interactive OpenAPI UI (redirect to /docs/api) |
| GET | /api/openapi.json | 🔓 (local) | OpenAPI 3 schema (JSON) |

## OPD (read-only, OPD-scoped)
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/opds | 🔒 | Filters: `search`. Counts + pagu sum included |
| GET | /api/v1/opds/{opd} | 🔒 | Scoped: OPD users only their own |

## Programs / Kegiatan / Sub Kegiatan (budget hierarchy)
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/programs | 🔒 | Filters: `opd_id`, `tahun_anggaran_id`, `search` |
| POST | /api/v1/programs | 🔒 | `kode_program` unique |
| GET | /api/v1/programs/{program} | 🔒 | |
| PUT/PATCH | /api/v1/programs/{program} | 🔒 | |
| DELETE | /api/v1/programs/{program} | 🔒 | Blocked when belanja has commit/realisasi |
| GET | /api/v1/kegiatan | 🔒 | Filters: `program_id`, `opd_id` (admin), `tahun_anggaran_id`, `search` |
| POST | /api/v1/kegiatan | 🔒 | `program_id` required; `persentase` computed |
| GET | /api/v1/kegiatan/{kegiatan} | 🔒 | OPD-scoped |
| PUT/PATCH | /api/v1/kegiatan/{kegiatan} | 🔒 | |
| DELETE | /api/v1/kegiatan/{kegiatan} | 🔒 | Blocked when funds exist |
| GET | /api/v1/sub-kegiatan | 🔒 | Filters: `kegiatan_id`, `search` |
| POST | /api/v1/sub-kegiatan | 🔒 | Body includes `kegiatan_id` |
| GET | /api/v1/sub-kegiatan/{subKegiatan} | 🔒 | |
| PUT/PATCH | /api/v1/sub-kegiatan/{subKegiatan} | 🔒 | |
| DELETE | /api/v1/sub-kegiatan/{subKegiatan} | 🔒 | Blocked when funds exist |

## Belanja + financial actions 💰
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/belanja | 🔒 | Filters: `opd_id`, `sub_kegiatan_id`, `rekening_id`, `sumber_dana_id`, `tahun_anggaran_id`, `search` |
| POST | /api/v1/belanja | 🔒 | `dana_di_commit` starts 0; tahun anggaran defaults to active |
| GET | /api/v1/belanja/{belanja} | 🔒 | Exposes `available_pagu` |
| PUT/PATCH | /api/v1/belanja/{belanja} | 🔒 | Cannot reduce `realisasi`; `pagu >= commit + realisasi` |
| POST | /api/v1/belanja/{belanja}/commit | 🔒 💰 | Body: `amount`. Domain `Belanja::commit()` |
| POST | /api/v1/belanja/{belanja}/release | 🔒 💰 | Body: `amount`. Domain `Belanja::releaseCommit()` |
| POST | /api/v1/belanja/{belanja}/realize | 🔒 💰 | Body: `amount`. Domain `Belanja::realize()` |

## Penerimaan (revenue)
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/penerimaan | 🔒 | Filters: `opd_id`, `sumber_dana_id`, `rekening_id`, `tahun_anggaran_id`, `tanggal_dari/sampai`, `search` |
| POST | /api/v1/penerimaan | 🔒 | Rekening must be tipe `pendapatan`; nested `details` allowed |
| GET | /api/v1/penerimaan/{penerimaan} | 🔒 | `realisasi`/`persentase` computed from transactions |
| PUT/PATCH | /api/v1/penerimaan/{penerimaan} | 🔒 | |
| DELETE | /api/v1/penerimaan/{penerimaan} | 🔒 | Blocked when transactions exist |
| GET | /api/v1/penerimaan-details | 🔒 | Read-only flat list |
| GET | /api/v1/penerimaan-details/{detail} | 🔒 | |

## Transaksi Penerimaan + BKU 💰
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/transaksi-penerimaan | 🔒 | Filters: `penerimaan_id`, `tanggal_dari/sampai`, `search` |
| POST | /api/v1/transaksi-penerimaan | 🔒 💰 | `nomor_registrasi` auto (REG-XXXXX/YYYY); nested `bkus` validated; BKU sum rule |
| GET | /api/v1/transaksi-penerimaan/{transaksiPenerimaan} | 🔒 | |
| PUT/PATCH | /api/v1/transaksi-penerimaan/{transaksiPenerimaan} | 🔒 💰 | Full BKU sync (replace semantics, web-parity); `nomor_registrasi` immutable |
| DELETE | /api/v1/transaksi-penerimaan/{transaksiPenerimaan} | 🔒 | |
| POST | /api/v1/transaksi-penerimaan/{transaksiPenerimaan}/bkus | 🔒 💰 | Single BKU add; re-checks SUM(BKU) == realisasi |
| PATCH | /api/v1/transaksi-penerimaan/{transaksiPenerimaan}/bkus/{bku} | 🔒 💰 | Single BKU update; re-checks sum |
| DELETE | /api/v1/transaksi-penerimaan/{transaksiPenerimaan}/bkus/{bku} | 🔒 💰 | |

## Pengeluaran
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/pengeluaran | 🔒 | Filters: `opd_id`, `kegiatan_id`, `sub_kegiatan_id`, `belanja_id`, `sumber_dana_id`, `rekening_id`, `tanggal_dari/sampai`, `search` |
| POST | /api/v1/pengeluaran | 🔒 | Hierarchy consistency enforced; rekening tipe `belanja`; `persentase` computed |
| GET | /api/v1/pengeluaran/{pengeluaran} | 🔒 | |
| PUT/PATCH | /api/v1/pengeluaran/{pengeluaran} | 🔒 | |
| DELETE | /api/v1/pengeluaran/{pengeluaran} | 🔒 | |

## Posisi Kas
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/posisi-kas | 🔒 | Filters: `opd_id`, `rekening_id`, `tanggal_dari/sampai` |
| POST | /api/v1/posisi-kas | 🔒 | Only kas **leaf** rekening; `saldo_akhir` computed |
| GET | /api/v1/posisi-kas/{posisiKas} | 🔒 | |
| PUT/PATCH | /api/v1/posisi-kas/{posisiKas} | 🔒 | |
| DELETE | — | ❌ | Not exposed (matches read/report focus; no destructive op in web either for leaves in use) |

## Master data
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/rekenings | 🔒 | Filters: `tipe`, `parent_id`, `kas_leaf=1`, `search` |
| GET | /api/v1/rekenings/{rekening} | 🔒 | |
| POST | /api/v1/rekenings | 👑 | Kas hierarchy validated |
| PUT/PATCH | /api/v1/rekenings/{rekening} | 👑 | Cycle + self-parent guard |
| DELETE | /api/v1/rekenings/{rekening} | 👑 | Blocked when children exist |
| GET | /api/v1/sumber-dana | 🔒 | `search` |
| POST | /api/v1/sumber-dana | 🔒 | |
| GET | /api/v1/sumber-dana/{sumberDana} | 🔒 | |
| PUT/PATCH | /api/v1/sumber-dana/{sumberDana} | 🔒 | |
| DELETE | /api/v1/sumber-dana/{sumberDana} | 🔒 | |
| GET | /api/v1/rekening-banks | 🔒 | Global master; filters: `active=1`, `search` |
| GET | /api/v1/rekening-banks-active | 🔒 | Convenience: active banks only |
| GET | /api/v1/rekening-banks/{rekeningBank} | 🔒 | |
| POST | /api/v1/rekening-banks | 🔒 | Unique `account_number` |
| PUT/PATCH | /api/v1/rekening-banks/{rekeningBank} | 🔒 | Can toggle `is_active` |
| DELETE | /api/v1/rekening-banks/{rekeningBank} | 🔒 | Blocked when used by BKU rows |
| GET | /api/v1/tahun-anggaran | 🔒 | |
| GET | /api/v1/tahun-anggaran-active | 🔒 | Currently active fiscal year |
| POST | /api/v1/tahun-anggaran | 👑 | Unique `tahun` |
| PUT/PATCH | /api/v1/tahun-anggaran/{tahunAnggaran} | 👑 | `status: open|closed` |
| POST | /api/v1/tahun-anggaran/{tahunAnggaran}/activate | 👑 | Single-active-year invariant; audited |

## Permintaan Dana workflow 💰
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/permintaan-dana | 🔒 | Filters: `status`, `opd_id` (admin), `sumber_dana_id`, `tanggal_dari/sampai`, `search` |
| POST | /api/v1/permintaan-dana | 🔒 | Auto `PD-XXXX/YYYY`; status = draft; tahun anggaran = active |
| GET | /api/v1/permintaan-dana/{permintaanDana} | 🔒 | |
| PUT/PATCH | /api/v1/permintaan-dana/{permintaanDana} | 🔒 | Only draft/ditolak; status ignored from client |
| POST | /api/v1/permintaan-dana/{permintaanDana}/submit | 🔒 💰 | draft→menunggu; commits Belanja; 422 on business failure |
| POST | /api/v1/permintaan-dana/{permintaanDana}/approve | 👑 💰 | menunggu→disetujui; realizes Belanja; records Persetujuan |
| POST | /api/v1/permintaan-dana/{permintaanDana}/reject | 👑 💰 | menunggu→ditolak; releases funds; records rejection |
| GET | /api/v1/permintaan-dana/{permintaanDana}/persetujuan | 🔒 | Approval history |

## Transfer Dana
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/transfer-dana | 🔒 | Filters: `status`, `opd_id` (admin), `tanggal_dari/sampai`, `search` |
| POST | /api/v1/transfer-dana | 🔒 | Auto `TF-XXXX/YYYY`; status = draft |
| GET | /api/v1/transfer-dana/{transferDana} | 🔒 | |
| PUT/PATCH | /api/v1/transfer-dana/{transferDana} | 🔒 | `selesai` is final; sets `tanggal_selesai` |
| DELETE | /api/v1/transfer-dana/{transferDana} | 🔒 | Blocked when `selesai` |

## Dashboard
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/dashboard | 🔒 | Full snapshot (pagu/realisasi/commit/available, revenue, expenditure, saldo kas, status counts) |
| GET | /api/v1/dashboard/summary | 🔒 | Alias of index |
| GET | /api/v1/dashboard/budget | 🔒 | + percentage |
| GET | /api/v1/dashboard/revenue | 🔒 | Date range filter |
| GET | /api/v1/dashboard/expenditure | 🔒 | Date range filter |
| GET | /api/v1/dashboard/cash | 🔒 | kas penerimaan/pengeluaran/saldo |
| GET | /api/v1/dashboard/programs | 🔒 | Per-program derived totals |
| GET | /api/v1/dashboard/activity | 🔒 | 6 latest permintaan |

## Reports (JSON + CSV export)
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/reports/penerimaan | 🔒 | Rows + `meta.total_target/total_realisasi/persentase` |
| GET | /api/v1/reports/pengeluaran | 🔒 | Rows + `meta.total_anggaran/total_realisasi/persentase` |
| GET | /api/v1/reports/posisi-kas | 🔒 | Rows + saldo totals |
| GET | /api/v1/reports/permintaan-dana | 🔒 | Rows + status breakdown |
| GET | /api/v1/reports/penerimaan/export | 🔒 | Streamed CSV |
| GET | /api/v1/reports/pengeluaran/export | 🔒 | Streamed CSV |
| GET | /api/v1/reports/posisi-kas/export | 🔒 | Streamed CSV |
| GET | /api/v1/reports/permintaan-dana/export | 🔒 | Streamed CSV |

## AI (read-only, same authorization) 🔒
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/ai/summary | 🔒 | Fiscal year, OPD, budget block, revenue, expenditure, cash, fund requests |
| GET | /api/v1/ai/budget-summary | 🔒 | pagu/realisasi/commit/available + percentages |
| GET | /api/v1/ai/revenue-summary | 🔒 | target vs realisasi |
| GET | /api/v1/ai/expenditure-summary | 🔒 | realisasi vs pagu |
| GET | /api/v1/ai/cash-summary | 🔒 | |
| GET | /api/v1/ai/program-summary | 🔒 | Per-program aggregates |
| GET | /api/v1/ai/financial-alerts | 🔒 | Low availability, high realization, pending requests, transfers in progress, exhausted lines |

## Notifications 🔒
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/notifications | 🔒 | Own only + `meta.unread_count` |
| GET | /api/v1/notifications/unread | 🔒 | |
| POST | /api/v1/notifications/{notification}/read | 🔒 | Ownership enforced |
| POST | /api/v1/notifications/read-all | 🔒 | |

## Audit Logs 👑
| Method | URI | Access | Notes |
|---|---|---|---|
| GET | /api/v1/audit-logs | 👑 | Filters: `user_id`, `action`, `model`, `model_id`, `tanggal_dari/sampai` |
