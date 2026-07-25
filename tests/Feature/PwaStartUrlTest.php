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

// Installed PWAs cache the manifest they were installed with, so an older
// install still launches at /attendance. Nothing in the app links there —
// every "Masuk" button uses attendance.selfie — so send it to beranda.
test('the legacy /attendance url redirects to beranda instead of opening the camera', function () {
    $role = App\Models\Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );
    $user = App\Models\User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get('/attendance')
        ->assertRedirect('/attendance/dashboard');
});
