<?php

test('all pages render for admin', function () {
    $d = seedFullDataset();

    $this->actingAs($d['admin']);

    $this->get('/dashboard')->assertSuccessful();
    $this->get('/opd')->assertSuccessful();
    $this->get("/opd/{$d['opd']->id}")->assertSuccessful();
    $this->get('/upt')->assertSuccessful();
    $this->get('/upt/create')->assertSuccessful();
    $this->get("/upt/{$d['upt']->id}/edit")->assertSuccessful();
    $this->get('/sumber-dana')->assertSuccessful();
    $this->get('/sumber-dana/create')->assertSuccessful();
    $this->get("/sumber-dana/{$d['sumberDana']->id}/edit")->assertSuccessful();
    $this->get('/rekening-kas')->assertSuccessful();
    $this->get('/rekening-kas/create')->assertSuccessful();
    $this->get("/rekening-kas/{$d['rekening']->id}/edit")->assertSuccessful();
    $this->get('/program-kegiatan')->assertSuccessful();
    $this->get('/program-kegiatan/create')->assertSuccessful();
    $this->get("/program-kegiatan/{$d['program']->id}/edit")->assertSuccessful();
    $this->get("/kegiatan/{$d['kegiatan']->id}/sub-kegiatan")->assertSuccessful();
    $this->get("/kegiatan/{$d['kegiatan']->id}/sub-kegiatan/create")->assertSuccessful();
    $this->get("/kegiatan/{$d['kegiatan']->id}/sub-kegiatan/{$d['subKegiatan']->id}/edit")->assertSuccessful();
    $this->get("/sub-kegiatan/{$d['subKegiatan']->id}/belanja")->assertSuccessful();
    $this->get("/sub-kegiatan/{$d['subKegiatan']->id}/belanja/create")->assertSuccessful();
    $this->get("/sub-kegiatan/{$d['subKegiatan']->id}/belanja/{$d['belanja']->id}/edit")->assertSuccessful();
    $this->get('/master-data/penerimaan')->assertSuccessful();
    $this->get('/master-data/penerimaan/create')->assertSuccessful();
    $this->get('/transaksi-penerimaan')->assertSuccessful();
    $this->get('/transaksi-penerimaan/create')->assertSuccessful();
    $this->get('/pengeluaran')->assertSuccessful();
    $this->get('/pengeluaran/create')->assertSuccessful();
    $this->get('/posisi-kas')->assertSuccessful();
    $this->get('/posisi-kas/create')->assertSuccessful();
    $this->get('/permintaan-dana')->assertSuccessful();
    $this->get('/permintaan-dana/create')->assertSuccessful();
    $this->get("/permintaan-dana/{$d['permintaanDraft']->id}/edit")->assertSuccessful();
    $this->get('/persetujuan')->assertSuccessful();
    $this->get('/transfer-dana')->assertSuccessful();
    $this->get('/transfer-dana/create')->assertSuccessful();
    $this->get('/laporan-penerimaan')->assertSuccessful();
    $this->get('/laporan-pengeluaran')->assertSuccessful();
    $this->get('/laporan-posisi-kas')->assertSuccessful();
    $this->get('/rekap-permintaan-dana')->assertSuccessful();
    $this->get('/notifications')->assertSuccessful();
    $this->get('/pengaturan')->assertSuccessful();
    $this->get('/user-management')->assertSuccessful();
    $this->get('/user-management/create')->assertSuccessful();
    $this->get("/user-management/{$d['user']->id}/edit")->assertSuccessful();
    $this->get('/tahun-anggaran')->assertSuccessful();
    $this->get('/profile')->assertSuccessful();
});

test('all pages render for opd user', function () {
    $d = seedFullDataset();

    $this->actingAs($d['user']);

    $pages = [
        '/dashboard', '/opd', "/opd/{$d['opd']->id}",
        '/upt', '/upt/create', "/upt/{$d['upt']->id}/edit",
        '/sumber-dana', '/sumber-dana/create', "/sumber-dana/{$d['sumberDana']->id}/edit",
        '/rekening-kas',
        '/program-kegiatan', '/program-kegiatan/create',
        "/kegiatan/{$d['kegiatan']->id}/sub-kegiatan",
        "/kegiatan/{$d['kegiatan']->id}/sub-kegiatan/create",
        "/kegiatan/{$d['kegiatan']->id}/sub-kegiatan/{$d['subKegiatan']->id}/edit",
        "/sub-kegiatan/{$d['subKegiatan']->id}/belanja",
        "/sub-kegiatan/{$d['subKegiatan']->id}/belanja/create",
        "/sub-kegiatan/{$d['subKegiatan']->id}/belanja/{$d['belanja']->id}/edit",
        '/master-data/penerimaan', '/master-data/penerimaan/create',
        '/transaksi-penerimaan', '/transaksi-penerimaan/create',
        '/pengeluaran', '/pengeluaran/create',
        '/posisi-kas', '/posisi-kas/create',
        '/permintaan-dana', '/permintaan-dana/create', "/permintaan-dana/{$d['permintaanDraft']->id}/edit",
        '/transfer-dana', '/transfer-dana/create',
        '/laporan-penerimaan', '/laporan-pengeluaran', '/laporan-posisi-kas',
        '/rekap-permintaan-dana', '/notifications', '/pengaturan', '/profile',
    ];

    foreach ($pages as $page) {
        $response = $this->get($page);
        if ($response->status() >= 300) {
            throw new RuntimeException("FAILED: {$page} returned {$response->status()}");
        }
        $response->assertSuccessful($page);
    }
});

test('opd user cannot access admin only pages', function () {
    $d = seedFullDataset();

    $this->actingAs($d['user'])
        ->get('/user-management')
        ->assertForbidden();

    $this->actingAs($d['user'])
        ->get('/user-management/create')
        ->assertForbidden();

    $this->actingAs($d['user'])
        ->get('/tahun-anggaran')
        ->assertForbidden();

    $this->actingAs($d['user'])
        ->get('/persetujuan')
        ->assertForbidden();
});
