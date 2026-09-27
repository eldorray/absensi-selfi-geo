<?php

// tests/Feature/Guru/GuruComponentsTest.php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

test('list-item renders a link with a plain title node and a chevron', function () {
    $html = Blade::render('<x-guru.list-item href="/izin" icon="doc" title="Kesiswaan" desc="Direktori siswa" />');

    expect($html)
        ->toContain('href="/izin"')
        ->toContain('<span class="g-list__title">Kesiswaan</span>')
        ->toContain('class="g-list__desc">Direktori siswa</span>')
        ->toContain('g-list__chev')
        ->toContain('aria-hidden="true"');
});

test('list-item without href renders a div without chevron', function () {
    $html = Blade::render('<x-guru.list-item title="Statis" />');

    expect($html)->toContain('<div class="g-list__item"')->not->toContain('g-list__chev');
});

test('button renders an anchor with href and a button otherwise', function () {
    expect(Blade::render('<x-guru.button href="/x" variant="hero">Absen</x-guru.button>'))
        ->toContain('<a href="/x"')->toContain('g-btn g-btn--hero');
    expect(Blade::render('<x-guru.button type="submit">Kirim</x-guru.button>'))
        ->toContain('<button type="submit"')->toContain('g-btn--primary');
});

test('chip, stat and card apply their modifier classes', function () {
    expect(Blade::render('<x-guru.chip tone="attn">3</x-guru.chip>'))->toContain('g-chip g-chip--attn');
    expect(Blade::render('<x-guru.stat value="17" label="Tepat waktu" tone="primary" />'))
        ->toContain('g-stat__value g-stat__value--primary')->toContain('>17<')->toContain('Tepat waktu');
    expect(Blade::render('<x-guru.card variant="hero" aria-label="Presensi">x</x-guru.card>'))
        ->toContain('<section class="g-card g-card--hero" aria-label="Presensi">');
});

test('field wires label, required marker and validation error', function () {
    // In HTTP requests ShareErrorsFromSession shares $errors with every view; Blade::render skips middleware.
    view()->share('errors', (new ViewErrorBag)->put('default', new MessageBag(['name' => ['Nama wajib diisi.']])));

    $html = Blade::render('<x-guru.field label="Nama" for="name" error="name" :required="true"><input id="name"></x-guru.field>');

    expect($html)
        ->toContain('<label for="name" class="g-label">')
        ->toContain('class="g-req" aria-hidden="true">*</span>')
        ->toContain('<p id="name-error" class="g-error">Nama wajib diisi.</p>');
});

test('icon renders known paths and an empty svg for unknown names', function () {
    expect(Blade::render('<x-guru.icon name="bell" />'))->toContain('viewBox="0 0 24 24"')->toContain('<path');
    expect(Blade::render('<x-guru.icon name="nope" />'))->toContain('<svg')->not->toContain('<path');
});

test('empty and notice render their slots', function () {
    expect(Blade::render('<x-guru.empty icon="inbox" title="Belum ada">Isi nanti.</x-guru.empty>'))
        ->toContain('<h2>Belum ada</h2>')->toContain('Isi nanti.');
    expect(Blade::render('<x-guru.notice tone="error" title="Gagal">Coba lagi.</x-guru.notice>'))
        ->toContain('g-notice g-notice--error')->toContain('<strong>Gagal</strong>');
});
