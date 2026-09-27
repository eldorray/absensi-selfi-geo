<?php

// tests/Feature/Employee/AbsenPageTest.php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

afterEach(fn () => Carbon::setTestNow());

function absenTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create(['name' => 'Kantor MI', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);

    return User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
}

function absenCheckedIn(User $user, ?string $out = null): void
{
    Attendance::create(['user_id' => $user->id, 'status' => AttendanceStatus::Present, 'image_path' => 'x.jpg', 'check_in_lat' => -6.2, 'check_in_long' => 106.8, 'distance_meters' => 12, 'check_out_at' => $out]);
}

test('check-in renders camera, status chips, office and submit', function () {
    $this->actingAs(absenTeacher())->get(route('attendance.selfie'))
        ->assertSuccessful()
        ->assertSee('x-data="attendanceForm()"', false)
        ->assertSee('class="g-camera"', false)
        ->assertSee('aria-live="polite"', false)
        ->assertSee('Kantor tujuan')
        ->assertSee('Terkunci oleh admin')
        ->assertSee('Ambil Foto Manual')
        ->assertSee('Kirim Absen Masuk');
});

test('check-out explains each unavailable state', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = absenTeacher();

    $this->actingAs($teacher)->get(route('attendance.checkout'))
        ->assertSee('Belum absen masuk')
        ->assertSee('href="'.route('attendance.selfie').'"', false);

    absenCheckedIn($teacher);
    $this->actingAs($teacher)->get(route('attendance.checkout'))
        ->assertSee('Belum waktunya pulang')
        ->assertSee('Absen pulang dibuka pukul 15.30');

    Attendance::where('user_id', $teacher->id)->update(['check_out_at' => '2026-07-20 09:55:00']);
    $this->actingAs($teacher)->get(route('attendance.checkout'))
        ->assertSee('Absen pulang tercatat')
        ->assertSee('09.55');
});

test('check-out form shows once the window is open', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 16:00:00'));
    $teacher = absenTeacher();
    absenCheckedIn($teacher);

    $this->actingAs($teacher)->get(route('attendance.checkout'))
        ->assertSee('x-data="checkoutForm()"', false)
        ->assertSee('Kirim Absen Pulang');
});

test('attendance templates keep their Alpine logic and drop the legacy classes', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/{$view}.blade.php"));
    $partial = file_get_contents(resource_path('views/attendance/partials/absen-form.blade.php'));

    expect($template)
        ->toContain('submitErrorMessage(response, data)')
        ->toContain('takeManualPhoto() {')
        ->toContain("formData.append('liveness_verified'")
        ->not->toContain('glass-card')
        ->not->toContain('theme-')
        ->and($partial)->not->toContain('glass-card')->not->toContain('theme-');
})->with(['selfie', 'checkout']);
