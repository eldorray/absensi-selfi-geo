<?php

// tests/Feature/Guru/GuruCleanupTest.php

use Illuminate\Support\Facades\File;

test('teacher views no longer carry the previous design classes', function () {
    $legacy = ['glass-card', 'theme-text-', 'theme-input', 'theme-btn-submit', 'solid-panel', 'font-outfit', 'animate-stagger', 'pwa-m3'];
    // Di luar cakupan: kamera lama (x-layouts.app, tidak dipakai route) dan tiga halaman kesiswaan ber-layout admin.
    $skip = ['create.blade.php', 'kesiswaan/notifications.blade.php', 'kesiswaan/referral-form.blade.php', 'kesiswaan/referral.blade.php'];

    $paths = collect(File::allFiles(resource_path('views/attendance')))
        ->reject(fn ($file) => in_array($file->getRelativePathname(), $skip, true))
        ->map(fn ($file) => $file->getPathname())
        ->push(resource_path('views/components/layouts/mobile.blade.php'));

    $offenders = $paths
        ->filter(fn (string $path) => str(file_get_contents($path))->contains($legacy))
        ->map(fn (string $path) => str_replace(resource_path('views/'), '', $path))
        ->values()
        ->all();

    expect($offenders)->toBe([]);
});

test('the offline page uses the guru palette', function () {
    $html = file_get_contents(public_path('offline.html'));

    expect($html)->toContain('#F4F1EA')->toContain('#1E5A48')->toContain('prefers-color-scheme: dark')->toContain('prefers-reduced-motion');
});

test('the offline page uses a light green primary in dark mode for icon and focus contrast', function () {
    $html = file_get_contents(public_path('offline.html'));

    expect($html)->toMatch('/prefers-color-scheme:\s*dark\)\s*\{\s*:root\s*\{[^}]*--primary:\s*#8FD1B5/');
});
