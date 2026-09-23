<?php

use App\Models\Opd;
use App\Models\Rekening;
use App\Models\User;

test('admin can create a kas detail under a kas parent', function () {
    $admin = User::factory()->admin()->create();
    $kasInduk = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk XYZ', 'tipe' => 'kas']);

    $this->actingAs($admin)
        ->post('/rekening-kas', [
            'kode' => '1.1.1.01',
            'nama' => 'Kas Tunai XYZ',
            'tipe' => 'kas',
            'parent_id' => $kasInduk->id,
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('rekenings', [
        'kode' => '1.1.1.01',
        'tipe' => 'kas',
        'parent_id' => $kasInduk->id,
    ]);
});

test('cannot create a kas detail under a pendapatan rekening', function () {
    $admin = User::factory()->admin()->create();
    $pendapatan = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan XYZ', 'tipe' => 'pendapatan']);

    $this->actingAs($admin)
        ->from('/rekening-kas/create')
        ->post('/rekening-kas', [
            'kode' => '1.1.1.01',
            'nama' => 'Kas Detail XYZ',
            'tipe' => 'kas',
            'parent_id' => $pendapatan->id,
        ])
        ->assertSessionHasErrors([
            'parent_id' => 'Hanya rekening bertipe kas yang dapat memiliki rekening detail kas.',
        ]);

    $this->assertDatabaseMissing('rekenings', ['kode' => '1.1.1.01']);
});

test('cannot create a non-kas child under a kas parent', function () {
    $admin = User::factory()->admin()->create();
    $kasInduk = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk XYZ', 'tipe' => 'kas']);

    $this->actingAs($admin)
        ->from('/rekening-kas/create')
        ->post('/rekening-kas', [
            'kode' => '4.1.9',
            'nama' => 'Pendapatan XYZ',
            'tipe' => 'pendapatan',
            'parent_id' => $kasInduk->id,
        ])
        ->assertSessionHasErrors([
            'tipe' => 'Rekening detail kas harus bertipe kas.',
        ]);

    $this->assertDatabaseMissing('rekenings', ['kode' => '4.1.9']);
});

test('a rekening cannot be its own parent', function () {
    $admin = User::factory()->admin()->create();
    $kasInduk = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk XYZ', 'tipe' => 'kas']);

    $this->actingAs($admin)
        ->from("/rekening-kas/{$kasInduk->id}/edit")
        ->put("/rekening-kas/{$kasInduk->id}", [
            'kode' => '1.1.1',
            'nama' => 'Kas Induk XYZ',
            'tipe' => 'kas',
            'parent_id' => $kasInduk->id,
        ])
        ->assertSessionHasErrors([
            'parent_id' => 'Rekening tidak dapat menjadi detail dari dirinya sendiri.',
        ]);

    expect($kasInduk->fresh()->parent_id)->toBeNull();
});

test('a circular hierarchy is rejected', function () {
    $admin = User::factory()->admin()->create();
    $a = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas A XYZ', 'tipe' => 'kas']);
    $b = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas B XYZ', 'tipe' => 'kas', 'parent_id' => $a->id]);
    $c = Rekening::create(['kode' => '1.1.1.01.01', 'nama' => 'Kas C XYZ', 'tipe' => 'kas', 'parent_id' => $b->id]);

    // Making A a child of its own grandchild C would loop A -> B -> C -> A.
    $this->actingAs($admin)
        ->from("/rekening-kas/{$a->id}/edit")
        ->put("/rekening-kas/{$a->id}", [
            'kode' => '1.1.1',
            'nama' => 'Kas A XYZ',
            'tipe' => 'kas',
            'parent_id' => $c->id,
        ])
        ->assertSessionHasErrors([
            'parent_id' => 'Hierarki rekening tidak boleh melingkar.',
        ]);

    expect($a->fresh()->parent_id)->toBeNull();
});

test('an induk with children cannot lose its kas type', function () {
    $admin = User::factory()->admin()->create();
    $a = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas A XYZ', 'tipe' => 'kas']);
    $b = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas B XYZ', 'tipe' => 'kas', 'parent_id' => $a->id]);

    $this->actingAs($admin)
        ->from("/rekening-kas/{$a->id}/edit")
        ->put("/rekening-kas/{$a->id}", [
            'kode' => '1.1.1',
            'nama' => 'Kas A XYZ',
            'tipe' => 'pendapatan',
        ])
        ->assertSessionHasErrors([
            'tipe' => 'Rekening induk tidak dapat diubah tipenya karena masih memiliki detail.',
        ]);

    expect($a->fresh()->tipe)->toBe('kas')
        ->and($b->fresh()->parent_id)->toBe($a->id);
});

test('admin can move a kas detail to a different parent and detach it', function () {
    $admin = User::factory()->admin()->create();
    $a = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas A XYZ', 'tipe' => 'kas']);
    $b = Rekening::create(['kode' => '1.1.2', 'nama' => 'Kas B XYZ', 'tipe' => 'kas']);
    $child = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas Child XYZ', 'tipe' => 'kas', 'parent_id' => $a->id]);

    $this->actingAs($admin)
        ->put("/rekening-kas/{$child->id}", [
            'kode' => '1.1.1.01',
            'nama' => 'Kas Child XYZ',
            'tipe' => 'kas',
            'parent_id' => $b->id,
        ])
        ->assertSessionHasNoErrors();

    expect($child->fresh()->parent_id)->toBe($b->id);

    $this->actingAs($admin)
        ->put("/rekening-kas/{$child->id}", [
            'kode' => '1.1.1.01',
            'nama' => 'Kas Child XYZ',
            'tipe' => 'kas',
            'parent_id' => null,
        ])
        ->assertSessionHasNoErrors();

    expect($child->fresh()->parent_id)->toBeNull();
});

test('a parent rekening cannot be deleted while it has children', function () {
    $admin = User::factory()->admin()->create();
    $a = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas A XYZ', 'tipe' => 'kas']);
    $b = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas B XYZ', 'tipe' => 'kas', 'parent_id' => $a->id]);

    $this->actingAs($admin)
        ->from('/rekening-kas')
        ->delete("/rekening-kas/{$a->id}")
        ->assertSessionHasErrors(['rekening' => 'Rekening memiliki rekening detail sehingga tidak dapat dihapus.']);

    $this->assertDatabaseHas('rekenings', ['id' => $a->id, 'kode' => '1.1.1']);
});

test('a child rekening can be deleted once it has no children', function () {
    $admin = User::factory()->admin()->create();
    $a = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas A XYZ', 'tipe' => 'kas']);
    $b = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas B XYZ', 'tipe' => 'kas', 'parent_id' => $a->id]);

    $this->actingAs($admin)
        ->delete("/rekening-kas/{$b->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('rekenings', ['id' => $b->id]);
});

test('opd user cannot manage rekening kas', function () {
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $user = User::factory()->create(['role' => 'opd', 'opd_id' => $opd->id]);
    $a = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas A XYZ', 'tipe' => 'kas']);

    $this->actingAs($user)
        ->post('/rekening-kas', [
            'kode' => '1.1.2',
            'nama' => 'Kas B XYZ',
            'tipe' => 'kas',
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete("/rekening-kas/{$a->id}")
        ->assertForbidden();
});

test('posisi kas only accepts leaf-level kas accounts', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $kasInduk = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk XYZ', 'tipe' => 'kas']);
    $kasChild = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas Tunai XYZ', 'tipe' => 'kas', 'parent_id' => $kasInduk->id]);
    $kasAlone = Rekening::create(['kode' => '1.1.2', 'nama' => 'Kas Kecil XYZ', 'tipe' => 'kas']);
    $pendapatan = Rekening::create(['kode' => '4.1.1', 'nama' => 'Pendapatan XYZ', 'tipe' => 'pendapatan']);

    // An induk (parent with children) is not a valid Posisi Kas target.
    $this->actingAs($admin)
        ->post('/posisi-kas', [
            'opd_id' => $opd->id,
            'rekening_id' => $kasInduk->id,
            'saldo_awal' => 1000000,
        ])
        ->assertSessionHasErrors('rekening_id');

    // A leaf kas detail is valid.
    $this->actingAs($admin)
        ->post('/posisi-kas', [
            'opd_id' => $opd->id,
            'rekening_id' => $kasChild->id,
            'saldo_awal' => 1000000,
        ])
        ->assertSessionHasNoErrors();

    // A standalone kas (no children) remains valid.
    $this->actingAs($admin)
        ->post('/posisi-kas', [
            'opd_id' => $opd->id,
            'rekening_id' => $kasAlone->id,
            'saldo_awal' => 500000,
        ])
        ->assertSessionHasNoErrors();

    // Non-kas rekenings are never accepted.
    $this->actingAs($admin)
        ->post('/posisi-kas', [
            'opd_id' => $opd->id,
            'rekening_id' => $pendapatan->id,
            'saldo_awal' => 500000,
        ])
        ->assertSessionHasErrors('rekening_id');
});

test('kas detail rekenings are selectable while induk rekenings are not', function () {
    $admin = User::factory()->admin()->create();
    $opd = Opd::create(['kode' => 'OPD-A', 'nama' => 'Dinas A']);
    $kasInduk = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk XYZ', 'tipe' => 'kas']);
    $kasChild = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas Tunai XYZ', 'tipe' => 'kas', 'parent_id' => $kasInduk->id]);

    $this->actingAs($admin)
        ->get('/posisi-kas/create')
        ->assertSuccessful()
        ->assertSee('Kas Tunai XYZ')
        ->assertDontSee('Kas Induk XYZ');
});

test('rekening kas index shows the hierarchy', function () {
    $admin = User::factory()->admin()->create();
    $kasInduk = Rekening::create(['kode' => '1.1.1', 'nama' => 'Kas Induk XYZ', 'tipe' => 'kas']);
    $kasChild = Rekening::create(['kode' => '1.1.1.01', 'nama' => 'Kas Tunai XYZ', 'tipe' => 'kas', 'parent_id' => $kasInduk->id]);

    $this->actingAs($admin)
        ->get('/rekening-kas')
        ->assertSuccessful()
        ->assertSee('Induk dari 1 detail')
        ->assertSee('Detail dari: 1.1.1 - Kas Induk XYZ');
});
