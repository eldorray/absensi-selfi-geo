<?php

use App\Models\Role;
use App\Models\User;

function teacherForVisualSystem(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

it('raises the whole navigation card and removes its top outline', function () {
    $styles = file_get_contents(resource_path('views/partials/pwa-material3.blade.php'));

    expect($styles)
        ->toContain('transform: translateY(-6px);')
        ->toContain('border-top: 0;')
        ->not->toContain('body.pwa-m3 .footer-nav::before');
});

it('renders the dashboard with the shared Material 3 attendance system', function () {
    $teacher = teacherForVisualSystem();

    $this->actingAs($teacher)
        ->get(route('attendance.dashboard'))
        ->assertSuccessful()
        ->assertSee('data-attendance-ui="material-3"', false)
        ->assertSee('data-m3-region="top-app-bar"', false)
        ->assertSee('data-profile-link="teacher-identity"', false)
        ->assertSee('href="'.route('attendance.profile').'"', false)
        ->assertSee('aria-label="Buka profil '.e($teacher->name).'"', false)
        ->assertSee('data-m3-region="content"', false)
        ->assertSee('data-m3-region="navigation-bar"', false)
        ->assertSee('aria-label="Navigasi utama"', false)
        ->assertSee('@view-transition', false)
        ->assertSee('navigation: auto', false)
        ->assertSee('@media (prefers-reduced-motion: reduce)', false)
        ->assertDontSee('maximum-scale=1', false)
        ->assertDontSee('user-scalable=no', false);
});

it('renders shared Material 3 shell pages while preserving their functional controls', function (string $routeName, string $expectedContent) {
    $teacher = teacherForVisualSystem();

    $this->actingAs($teacher)
        ->get(route($routeName))
        ->assertSuccessful()
        ->assertSee('data-attendance-ui="material-3"', false)
        ->assertSee('data-m3-region="content"', false)
        ->assertSee('aria-label="Navigasi utama"', false)
        ->assertSee($expectedContent);
})->with([
    'check-in camera' => ['attendance.selfie', 'Absensi Masuk'],
    'check-out flow' => ['attendance.checkout', 'Absensi Pulang'],
    'attendance history' => ['attendance.index', 'Riwayat Absen'],
    'profile form' => ['attendance.profile', 'Profil Saya'],
    'password form' => ['attendance.password', 'Ganti Password'],
    'leave list' => ['attendance.leaves.index', 'Perizinan Saya'],
    'leave create form' => ['attendance.leaves.create', 'Jenis Perizinan'],
]);

it('keeps the animated sheet treatment on secondary attendance pages', function () {
    $teacher = teacherForVisualSystem();

    $this->actingAs($teacher)
        ->get(route('attendance.profile'))
        ->assertSuccessful()
        ->assertSee('sheet-slide-up', false)
        ->assertSee('sheet-slide-up-anim', false)
        ->assertSee('class="sheet-handle"', false);
});

it('downscales browser selfie captures to a maximum dimension of 1024 pixels', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/{$view}.blade.php"));

    expect($template)
        ->toContain('const maxPhotoDimension = 1024;')
        ->toContain('Math.min(1, maxPhotoDimension / Math.max(video.videoWidth, video.videoHeight))')
        ->toContain('Math.round(video.videoWidth * photoScale)')
        ->toContain('Math.round(video.videoHeight * photoScale)')
        ->not->toContain('canvas.width = video.videoWidth;')
        ->not->toContain('canvas.height = video.videoHeight;');
})->with([
    'check-in camera' => ['selfie'],
    'check-out camera' => ['checkout'],
    'legacy attendance camera' => ['create'],
]);

it('redirects guests away from teacher attendance pages', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with([
    'dashboard' => ['attendance.dashboard'],
    'check-in' => ['attendance.selfie'],
    'check-out' => ['attendance.checkout'],
    'history' => ['attendance.index'],
    'profile' => ['attendance.profile'],
    'password' => ['attendance.password'],
    'leaves' => ['attendance.leaves.index'],
]);
