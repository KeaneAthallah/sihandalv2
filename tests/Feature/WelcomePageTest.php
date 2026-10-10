<?php

test('landing page shows live aggregate figures', function () {
    // seedFullDataset: 2 OPD, 2 sumber dana, pagu 200jt, realisasi 100jt,
    // penerimaan 400jt, pengeluaran 100jt.
    seedFullDataset();

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Total Pagu')
        ->assertSee('Sumber Dana')
        ->assertViewHas('sumberDanaCount', 2)
        ->assertViewHas('opdCount', 2)
        ->assertViewHas('totalPagu', fn ($value) => (float) $value === 200000000.0)
        ->assertViewHas('totalRealisasi', fn ($value) => (float) $value === 100000000.0)
        ->assertViewHas('persenRealisasi', fn ($value) => (float) $value === 50.0)
        ->assertViewHas('totalPenerimaan', fn ($value) => (float) $value === 400000000.0)
        ->assertViewHas('totalPengeluaran', fn ($value) => (float) $value === 100000000.0)
        ->assertSee('Rp 200 Juta')
        ->assertSee('Rp 400 Juta')
        ->assertSee('Rp 100 Juta')
        ->assertSee('50% terealisasi');
});

test('landing page renders for guests without any data', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertViewHas('sumberDanaCount', 0)
        ->assertViewHas('opdCount', 0)
        ->assertViewHas('totalPagu', fn ($value) => (float) $value === 0.0)
        ->assertViewHas('persenRealisasi', fn ($value) => (float) $value === 0.0);
});
