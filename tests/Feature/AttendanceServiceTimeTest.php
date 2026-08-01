<?php

declare(strict_types=1);

use App\Enums\AttendanceStatus;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSetting;
use App\Services\AttendanceService;
use Carbon\Carbon;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function timeTeacher(): User
{
    // Pola peran yang dipakai seluruh suite: `roles.slug` NOT NULL & unique,
    // dan tidak ada kolom `display_name`.
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create([
        'name' => 'MI Waktu',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);

    return User::create([
        'name' => 'Guru Waktu',
        'email' => 'waktu'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
        'office_id' => $office->id,
    ]);
}

afterEach(fn () => Carbon::setTestNow());

test('statusAt judges the supplied moment, not the clock', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 10:00:00'));
    $user = timeTeacher();
    $service = app(AttendanceService::class);
    $grace = WorkSetting::current()->after_check_in;

    $onTime = Carbon::parse('2026-08-03 07:00:00');
    $late = Carbon::parse('2026-08-03 07:00:00')->addMinutes($grace + 5);

    expect($service->statusAt($user, $onTime))->toBe(AttendanceStatus::Present)
        ->and($service->statusAt($user, $late))->toBe(AttendanceStatus::Late);
});

test('statusNow keeps judging the current clock', function () {
    $user = timeTeacher();
    $service = app(AttendanceService::class);
    $grace = WorkSetting::current()->after_check_in;

    Carbon::setTestNow(Carbon::parse('2026-08-03 07:00:00'));
    expect($service->statusNow($user))->toBe(AttendanceStatus::Present);

    Carbon::setTestNow(Carbon::parse('2026-08-03 07:00:00')->addMinutes($grace + 5));
    expect($service->statusNow($user))->toBe(AttendanceStatus::Late);
});

test('checkInWindowError judges the supplied moment', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 23:00:00'));
    $user = timeTeacher();
    $service = app(AttendanceService::class);

    // Jam server 23:00 sudah di luar jendela, tapi jam tangkap 07:00 masih di dalam.
    expect($service->checkInWindowError($user, Carbon::parse('2026-08-03 07:00:00')))->toBeNull()
        ->and($service->checkInWindowError($user))->not->toBeNull();
});
