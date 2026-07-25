<?php

declare(strict_types=1);

test('pwa manifest start_url points to the dashboard beranda, not the check-in form', function () {
    $manifest = json_decode((string) file_get_contents(public_path('manifest.json')), true);

    // Cold-opening the installed PWA must land on beranda (dashboard),
    // never the check-in form which auto-opens the camera.
    expect($manifest['start_url'])->toBe('/attendance/dashboard');
});

test('service worker precaches the dashboard beranda for offline launch', function () {
    $sw = (string) file_get_contents(public_path('sw.js'));

    expect($sw)->toContain("'/attendance/dashboard'");
});
