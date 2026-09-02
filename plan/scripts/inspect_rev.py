import json

d = json.load(open(r"D:\sihandalv2\storage\app\laporan\realisasi_pendapatan_januari_2026.json", encoding="utf-8"))
recs = d["records"]
for r in recs:
    print(
        "{:<18} {:<8} | {:<42} | target={:.2f} | bln_ini={:.2f} | sd_ini={:.2f} | %={:.2f} | lk={:.2f}".format(
            r["kode"], r["tipe_baris"], r["uraian"][:40],
            r["target"], r["realisasi_bulan_ini"], r["realisasi_sd_bulan_ini"],
            r["persentase"], r["lebih_kurang"],
        )
    )