<?php

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;

function teacherForVisualSystem(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

function homeroomForVisualSystem(User $teacher): void
{
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $class = SchoolClass::create(['school_level' => 'mi', 'name' => 'Kelas 4B', 'normalized_name' => SchoolClass::normalizeName('Kelas 4B'), 'grade_level' => 4, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);
}

it('renders teacher pages inside the AbsenKU Guru shell', function (string $routeName, string $expectedContent) {
    $this->actingAs(teacherForVisualSystem())
        ->get(route($routeName))
        ->assertSuccessful()
        ->assertSee('<body class="guru" data-teacher-ui="absenku-guru">', false)
        ->assertSee('data-region="content"', false)
        ->assertSee('aria-label="Navigasi utama"', false)
        ->assertSee('@view-transition', false)
        ->assertDontSee('maximum-scale=1', false)
        ->assertDontSee('user-scalable=no', false)
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

it('shows three tabs for a regular teacher and marks the current one', function () {
    $html = $this->actingAs(teacherForVisualSystem())
        ->get(route('attendance.index'))
        ->assertSuccessful()
        ->getContent();

    expect(substr_count($html, 'class="g-nav__item"'))->toBe(3)
        ->and($html)->toContain('href="'.route('attendance.index').'" class="g-nav__item" aria-current="page"')
        ->not->toContain('href="'.route('attendance.my-class.index').'" class="g-nav__item"');
});

it('adds the Kelas tab for a homeroom teacher', function () {
    $teacher = teacherForVisualSystem();
    homeroomForVisualSystem($teacher);

    $html = $this->actingAs($teacher)->get(route('attendance.index'))->assertSuccessful()->getContent();

    expect(substr_count($html, 'class="g-nav__item"'))->toBe(4)
        ->and($html)->toContain('href="'.route('attendance.my-class.index').'" class="g-nav__item"');
});

it('applies the saved or system theme before first paint', function () {
    $html = $this->actingAs(teacherForVisualSystem())->get(route('attendance.index'))->getContent();

    expect($html)
        ->toContain("localStorage.getItem('appearance') ?? localStorage.getItem('welcome-theme')")
        ->toContain("matchMedia('(prefers-color-scheme: dark)')")
        ->toContain("document.documentElement.classList.toggle('dark', dark)")
        ->and(strpos($html, 'window.guruApplyTheme'))->toBeLessThan(strpos($html, '<body'));
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
