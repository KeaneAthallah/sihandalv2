"""Convert BPKAD PDF reports into normalized JSON snapshots for SIHANDAL V2.

Reads:
  plan/1. Januari 2026.pdf            -> LAPORAN REALISASI PENDAPATAN DAERAH (Jan 2026)
  plan/POSKAS 08 JUNI 2026 Net.pdf   -> LAPORAN POSISI KAS DAERAH PER 08 JUNI 2026

Emits:
  storage/app/laporan/realisasi_pendapatan_januari_2026.json
  storage/app/laporan/posisi_kas_2026_06_08.json

These are aggregate report snapshots. They must NOT become individual
transaksipenerimaans/pengeluarans rows; they are stored intact under a
dedicated laporan_snapshots table and only surfaced for read-only reporting.
"""

from __future__ import annotations

import json
import re
import sys

import pdfplumber

PLAN = r"D:\sihandalv2\plan"
OUT = r"D:\sihandalv2\storage\app\laporan"

REVENUE_PDF = f"{PLAN}\\1. Januari 2026.pdf"
POSKAS_PDF = f"{PLAN}\\POSKAS 08 JUNI 2026 Net.pdf"
REVENUE_OUT = f"{OUT}\\realisasi_pendapatan_januari_2026.json"
POSKAS_OUT = f"{OUT}\\posisi_kas_2026_06_08.json"


def clean(value):
    """Null-safe whitespace strip."""
    if value is None:
        return ""
    return re.sub(r"\s+", " ", str(value)).strip()


def parse_amount(raw):
    """Parse a messy Indonesian rupiah amount into a float.

    Accepts '1 .518.122.833.722,17', '(2.417.622.545.267,13)', '-', '', '.',
    '4 21.414.300,00' -> returns 421.414.300 -> 421414300.00
    Negative amounts are wrapped in parentheses.
    """
    text = clean(raw)
    if not text or text in {"-", "."}:
        return 0.0
    if re.search(r"[A-Za-z]", text):
        return 0.0

    negative = text.startswith("(") and text.endswith(")")
    if negative:
        text = text[1:-1]

    text = text.replace(" ", "").replace(".", "").replace(",", ".")
    try:
        value = float(text)
    except ValueError:
        return 0.0

    return -round(value, 2) if negative else round(value, 2)


def build_kode(parts):
    """Join up to the first 6 kode columns into a dotted code string.

    Columns are separate single digits/groups in the PDF (e.g. '4','1','01','01').
    The resulting code looks like '4.1.01.01'. Empty leading cells that are
    legitimate subtotals keep their position.
    """
    out = [clean(p) for p in parts[:-1]]  # drop the last (often-empty 6th col for leaf)
    return ".".join(p for p in out if p) or ""


def parse_revenue_pdf():
    rows = []
    with pdfplumber.open(REVENUE_PDF) as pdf:
        for page in pdf.pages:
            for table in page.extract_tables():
                for raw in table:
                    if not raw or len(raw) < 14:
                        continue
                    cells = [clean(c) for c in raw[:14]]
                    jenis = cells[6]
                    # The grand-total line prints its label in column 0.
                    if not jenis and cells[0].startswith("TOTAL"):
                        jenis = cells[0]

                    # Skip the repeated column-header / column-number rows that
                    # start every page. They have no real chart-of-accounts code.
                    header_labels = {
                        "JENIS AYAT PENERIMAAN", "TARGET APBD TA. 2026", "JUMLAH REALISASI",
                        "BULAN INI", "S/ BULAN LALU D", "S/ BULAN INI D", "%",
                        "LEBIH / KURANG", "KET", "KODE REKENING", "D",
                    }
                    is_header = not jenis or jenis in header_labels or jenis.startswith("KODE")
                    is_number_row = len(cells[0]) == 1 and cells[0].isdigit() and \
                        cells[6].isdigit() and cells[7].isdigit()
                    if is_header or is_number_row:
                        continue  # skip header / blank separator rows

                    # A data row without any kode col but with total label = grand total.
                    is_total = jenis.startswith("TOTAL")
                    code_parts = [p for p in cells[:6] if p]
                    rows.append({
                        "section": "pendapatan",
                        "tipe_baris": "total" if is_total else "rincian",
                        "kode": build_kode(cells[:6]),
                        "level": len(code_parts),
                        "uraian": jenis,
                        "target": parse_amount(cells[7]),
                        "realisasi_bulan_ini": parse_amount(cells[8]),
                        "realisasi_sd_bulan_lalu": parse_amount(cells[9]),
                        "realisasi_sd_bulan_ini": parse_amount(cells[10]),
                        "persentase": parse_amount(cells[11]),
                        "lebih_kurang": parse_amount(cells[12]),
                        "keterangan": cells[13],
                    })
    return rows


def parse_poskas_pdf():
    """Extract three sections: saldo buku, saldo kas per bank, sumber dana breakouts.

    We read the raw tables (already well separated by pdfplumber) and tag each
    line with (bagian, sub, label, value...). Numeric and reconciliation checks
    (JUMLAH SALDO BUKU, JUMLAH SALDO KAS, JUMLAH SELISIH) are emitted as
    'total' rows so the importer can reconcile leaf sums against them.
    """
    records = []
    section = None
    sub = None

    def flush_rec(subject, uraian, vals, ltype="rincian"):
        if subject is None or not clean(uraian):
            return
        rec = {
            "section": subject,
            "sub": clean(sub or ""),
            "tipe_baris": ltype,
            "uraian": clean(uraian),
            "kode": "",
        }
        if subject == "saldo_kas_per_bank":
            rec["nilai"] = parse_amount(vals[0]) if len(vals) > 0 else 0.0
        elif subject in ("posisi_silpa_2025", "posisi_realisasi_2026"):
            rec["penerimaan"] = parse_amount(vals[0]) if len(vals) > 0 else 0.0
            rec["pengeluaran"] = parse_amount(vals[1]) if len(vals) > 1 else 0.0
            rec["sisa"] = parse_amount(vals[2]) if len(vals) > 2 else 0.0
        else:
            # saldo_buku / selisih: a single amount held in the first value.
            rec["nilai"] = parse_amount(vals[0]) if len(vals) > 0 else 0.0
        records.append(rec)

    with pdfplumber.open(POSKAS_PDF) as pdf:
        page1 = pdf.pages[0]
        for raw in page1.extract_tables()[0]:
            cells = [c or "" for c in raw]
            text = clean(cells[1]) if len(cells) > 1 else ""
            lower = text.lower()

            if text == "SALDO BUKU":
                section = "saldo_buku"
                continue
            if text == "SALDO KAS":
                section = "saldo_kas_per_bank"
                continue
            if "SELISIH" in text and section == "saldo_kas_per_bank":
                section = "selisih"
                continue

            if section == "saldo_buku":
                if text in {"PENERIMAAN", "PENGELUARAN", "JUMLAH SALDO BUKU"}:
                    ltype = "total" if text == "JUMLAH SALDO BUKU" else "rincian"
                    flush_rec("saldo_buku", text, [clean(cells[2]), clean(cells[3])], ltype)
            elif section == "saldo_kas_per_bank":
                if text == "JUMLAH SALDO KAS (JUMLAH I + JUMLAH II)" or text == "JUMLAH I" or text == "JUMLAH II":
                    flush_rec("saldo_kas_per_bank", text, [clean(cells[2]), clean(cells[3])], "total")
                elif clean(cells[0]).isdigit():
                    flush_rec("saldo_kas_per_bank", text, [clean(cells[2])])
            elif section == "selisih":
                if text.startswith("JUMLAH SELISIH"):
                    flush_rec("selisih", text, [clean(cells[3])], "total")
                elif clean(cells[0]).strip() and not lower.startswith("pengeluaran yang") and \
                        not lower.startswith("penerimaan yang") and not lower.startswith("selisih saldo kas dibanding") \
                        and not text.startswith("- "):
                    # numeric selisih detail rows (Pembulatan etc.)
                    val = clean(cells[3]) or clean(cells[2])
                    if val:
                        flush_rec("selisih", text, [val])

        page2 = pdf.pages[1]
        for raw in page2.extract_tables()[0]:
            cells = [c or "" for c in raw]
            joined = " ".join(clean(c) for c in cells)
            if "SALDO KAS BERDASARKAN BKU" in joined:
                continue
            if joined.strip() == "Saldo Kas termasuk :":
                continue
            tag = clean(cells[0])
            if tag in {"I", "II"}:
                sub = None
                if tag == "I":
                    section = "posisi_silpa_2025"
                else:
                    section = "posisi_realisasi_2026"
                continue
            if tag == "" and clean(cells[1]) in {"No", "Uraian"}:
                continue

            uraian = clean(cells[2]) if len(cells) > 2 else clean(cells[1])
            if uraian in {"No", "Uraian"}:
                continue

            # Empty uraian usually indicates a sub header line; detect via cells[1].
            if not uraian and clean(cells[1]):
                sub = clean(cells[1])
                continue

            row_vals = [clean(cells[3]), clean(cells[4]), clean(cells[5])] if len(cells) > 5 else \
                [clean(cells[3]), clean(cells[4])]
            ltype = "rincian"
            if uraian.startswith("Jumlah I") or uraian.startswith("Jumlah II") or \
                    uraian.startswith("TOTAL SISA SALDO KAS") or uraian.startswith("TOTAL SISA") or \
                    uraian.startswith("TOTAL"):
                ltype = "total"
            flush_rec(section, uraian, row_vals, ltype)

    return records


def main():
    rev = parse_revenue_pdf()
    pos_rows = parse_poskas_pdf()

    # Mark leaves: a row is a leaf if no other data row's kode is a strict
    # (deeper) descendant of its own kode.
    codes = [r["kode"] for r in rev if r["kode"]]
    for r in rev:
        k = r["kode"]
        r["is_leaf"] = bool(k) and not any(
            other != k and other.startswith(k + ".") for other in codes
        )

    revenue_doc = {
        "jenis": "realisasi_pendapatan",
        "judul": "LAPORAN REALISASI PENDAPATAN DAERAH",
        "periode": "BULAN JANUARI 2026",
        "tahun_anggaran": 2026,
        "tanggal_laporan": "2026-02-01",
        "signed_by": "Dra. FATNINI, M.Si",
        "pdf_file": "1. Januari 2026.pdf",
        "total_pendapatan": {
            "target": parse_amount("4.827.915.855.843,00"),
            "realisasi": parse_amount("453.374.970.858,87"),
        },
        "records": rev,
    }

    poskas_doc = {
        "jenis": "posisi_kas",
        "judul": "LAPORAN POSISI KAS DAERAH",
        "periode": "PER 08 JUNI 2026",
        "tahun_anggaran": 2026,
        "tanggal_laporan": "2026-06-08",
        "signed_by": "Dra. FATNINI, M.Si",
        "pdf_file": "POSKAS 08 JUNI 2026 Net.pdf",
        "saldo_buku_penerimaan": parse_amount("1.580.625.210.520,45"),
        "saldo_buku_pengeluaran": parse_amount("1.418.970.251.016,56"),
        "jumlah_saldo_buku": parse_amount("161.654.959.503,89"),
        "records": pos_rows,
    }

    with open(REVENUE_OUT, "w", encoding="utf-8") as f:
        json.dump(revenue_doc, f, ensure_ascii=False, indent=2)
    with open(POSKAS_OUT, "w", encoding="utf-8") as f:
        json.dump(poskas_doc, f, ensure_ascii=False, indent=2)

    print(f"realisasi_pendapatan: {len(rev)} rows -> {REVENUE_OUT}")
    print(f"posisi_kas: {len(pos_rows)} rows -> {POSKAS_OUT}")

    # Reconciliation: the three top-level group lines (4.1 + 4.2 + 4.3) must
    # equal the report's TOTAL PENDAPATAN DAERAH line.
    top_level = ["4.1", "4.2", "4.3"]
    subtotal = sum(r["realisasi_sd_bulan_ini"] for r in rev if r["kode"] in top_level)
    total_row = next((r for r in rev if r["tipe_baris"] == "total"), None)
    print(f"reconcile top-level({subtotal:,.2f}) vs TOTAL({total_row['realisasi_sd_bulan_ini']:,.2f}) "
          f"-> {'OK' if round(subtotal,2)==round(total_row['realisasi_sd_bulan_ini'],2) else 'MISMATCH'}")


if __name__ == "__main__":
    main()