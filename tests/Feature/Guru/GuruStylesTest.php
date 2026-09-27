<?php

// tests/Feature/Guru/GuruStylesTest.php

test('the teacher stylesheet is imported and scoped to .guru', function () {
    $app = file_get_contents(resource_path('css/app.css'));
    $guru = file_get_contents(resource_path('css/guru.css'));

    expect($app)->toContain('@import "./guru.css";')
        ->and($guru)
        ->toContain('.guru {')
        ->toContain('.dark .guru {')
        ->toContain('font-family: "Fraunces";')
        ->toContain('font-family: "Plus Jakarta Sans";')
        ->toContain('--g-primary: #1E5A48;')
        ->toContain('--g-ground: #F4F1EA;')
        ->toContain('@media (prefers-reduced-motion: reduce)');
});

test('the teacher fonts are self-hosted', function () {
    expect(filesize(resource_path('fonts/fraunces-latin.woff2')))->toBeGreaterThan(10_000)
        ->and(filesize(resource_path('fonts/plus-jakarta-sans-latin.woff2')))->toBeGreaterThan(10_000);
});
