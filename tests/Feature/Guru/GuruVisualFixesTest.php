<?php

use App\Models\User;

// Temuan ronde cek visual 1 (Task 13).

test('avatar initials use at most two letters', function () {
    expect((new User(['name' => 'Guru Uji Visual']))->initials())->toBe('GU')
        ->and((new User(['name' => 'Siti']))->initials())->toBe('S');
});

test('pagination labels are Indonesian', function () {
    expect(__('pagination.previous'))->toContain('Sebelumnya')
        ->and(__('pagination.next'))->toContain('Berikutnya');
});

test('beranda date line lets a long office name wrap below the date', function () {
    expect(file_get_contents(resource_path('css/guru.css')))
        ->toContain("    .g-dateline {\n        display: flex;\n        flex-wrap: wrap;");
});

test('neutral status chips stay visible on the page ground', function () {
    expect(file_get_contents(resource_path('css/guru.css')))->toContain('.g-status .g-chip--neutral {');
});

test('pagination styling is unlayered so framework dark utilities cannot override it', function () {
    $css = file_get_contents(resource_path('css/guru.css'));

    expect(strpos($css, '.guru .g-pager nav[role="navigation"] a'))->toBeGreaterThan(strrpos($css, '@layer components'));
});

test('bottom camera brackets sit above the face prompt', function () {
    expect(file_get_contents(resource_path('views/attendance/partials/absen-form.blade.php')))
        ->toContain('bottom-[76px] left-4')
        ->toContain('bottom-[76px] right-4');
});
