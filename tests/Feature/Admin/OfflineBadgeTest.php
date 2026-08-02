<?php

declare(strict_types=1);

use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function badgeAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::create([
        'name' => 'Admin Badge',
        'email' => 'badge'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
    ]);
}

test('the attendance list marks only rows that arrived from the offline queue', function () {
    $admin = badgeAdmin();
    $office = Office::create([
        'name' => 'MI Badge',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);
    $teacherRole = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    $queuedTeacher = User::create([
        'name' => 'Guru Antrian',
        'email' => 'guru'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $teacherRole->id,
        'office_id' => $office->id,
    ]);
    $liveTeacher = User::create([
        'name' => 'Guru Reguler',
        'email' => 'guru'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $teacherRole->id,
        'office_id' => $office->id,
    ]);

    // Arrived from the offline queue: synced_at is set.
    Attendance::create([
        'user_id' => $queuedTeacher->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
        'synced_at' => Carbon::parse('2026-08-03 10:00:00'),
    ]);
    // Recorded live: synced_at is null. Must NOT get the badge.
    Attendance::create([
        'user_id' => $liveTeacher->id,
        'status' => 'present',
        'image_path' => 'attendance/y.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
        'synced_at' => null,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.attendances.index'))
        ->assertOk()
        ->assertSee('Guru Antrian')
        ->assertSee('Guru Reguler');

    // Both rows are on the page, but only the queued one carries the
    // offline-queue tooltip. Counting occurrences (rather than a bare
    // assertSee/assertDontSee) is what actually fails if the @if wrapper
    // around the badge is ever removed and it starts rendering for every row.
    $html = $response->getContent();
    expect(substr_count($html, 'Dikirim dari antrean offline'))->toBe(1);
});

test('a row whose check-out alone came from the queue is still marked', function () {
    $admin = badgeAdmin();
    $office = Office::create([
        'name' => 'MI Badge Pulang',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);
    $teacherRole = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $teacher = User::create([
        'name' => 'Guru Pulang Antrian',
        'email' => 'guru'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $teacherRole->id,
        'office_id' => $office->id,
    ]);

    // Masuk online (synced_at null), pulang dari antrean. check_out_synced_at was
    // stored but never surfaced anywhere, so this row used to look fully online.
    Attendance::create([
        'user_id' => $teacher->id,
        'status' => 'present',
        'image_path' => 'attendance/z.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
        'synced_at' => null,
        'check_out_at' => Carbon::parse('2026-08-03 16:05:00'),
        'check_out_synced_at' => Carbon::parse('2026-08-03 18:00:00'),
    ]);

    $html = $this->actingAs($admin)
        ->get(route('admin.attendances.index'))
        ->assertOk()
        ->assertSee('Guru Pulang Antrian')
        ->getContent();

    expect(substr_count($html, 'Dikirim dari antrean offline'))->toBe(1)
        ->and($html)->toContain('pulang 03 Aug 2026 18:00');
});

test('the note names both halves when check-in and check-out both came from the queue', function () {
    $attendance = new Attendance([
        'synced_at' => Carbon::parse('2026-08-03 10:00:00'),
        'check_out_synced_at' => Carbon::parse('2026-08-03 18:00:00'),
    ]);

    expect($attendance->offlineSyncNote())
        ->toBe('Dikirim dari antrean offline — masuk 03 Aug 2026 10:00; pulang 03 Aug 2026 18:00');
});

test('a fully online row has no note at all', function () {
    expect((new Attendance)->offlineSyncNote())->toBeNull();
});
