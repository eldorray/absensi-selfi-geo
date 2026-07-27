<?php

declare(strict_types=1);

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

afterEach(fn () => Carbon::setTestNow());

function histTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

function histRecord(User $user, Carbon $at, AttendanceStatus $status = AttendanceStatus::Present, ?Carbon $out = null): Attendance
{
    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => $status,
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
        'check_out_at' => $out,
    ]);

    $attendance->created_at = $at;
    $attendance->save();

    return $attendance;
}

test('history returns the teacher records with meta', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 10:00:00'));
    $user = histTeacher();

    histRecord($user, Carbon::parse('2026-07-26 07:05:00'), AttendanceStatus::Present, Carbon::parse('2026-07-26 15:00:00'));
    histRecord($user, Carbon::parse('2026-07-25 07:40:00'), AttendanceStatus::Late);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/attendance/history')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [['id', 'date', 'check_in_time', 'check_out_time', 'status', 'image_url']],
            'meta' => ['current_page', 'last_page', 'total'],
        ]);

    // Newest first.
    $response->assertJsonPath('data.0.date', '2026-07-26')
        ->assertJsonPath('data.0.check_in_time', '07:05')
        ->assertJsonPath('data.0.check_out_time', '15:00')
        ->assertJsonPath('data.0.status', 'on_time')
        ->assertJsonPath('data.1.date', '2026-07-25')
        ->assertJsonPath('data.1.check_out_time', null)
        ->assertJsonPath('data.1.status', 'late')
        ->assertJsonPath('meta.total', 2);
});

test('history filters by the requested month', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 10:00:00'));
    $user = histTeacher();

    histRecord($user, Carbon::parse('2026-07-10 07:00:00'));
    histRecord($user, Carbon::parse('2026-06-10 07:00:00'));
    histRecord($user, Carbon::parse('2026-05-10 07:00:00'));

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/attendance/history?month=2026-06')
        ->assertStatus(200)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.date', '2026-06-10');
});

test('history rejects a malformed month', function () {
    $user = histTeacher();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/attendance/history?month=juli-2026')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['month']);
});

test('history paginates on the server', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 10:00:00'));
    $user = histTeacher();

    foreach (range(1, 25) as $day) {
        histRecord($user, Carbon::parse('2026-07-01 07:00:00')->addDays($day - 1));
    }

    $first = $this->actingAs($user, 'sanctum')
        ->getJson('/api/attendance/history')
        ->assertStatus(200)
        ->assertJsonPath('meta.total', 25)
        ->assertJsonPath('meta.current_page', 1);

    expect($first->json('meta.last_page'))->toBeGreaterThan(1);
    expect(count($first->json('data')))->toBeLessThan(25);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/attendance/history?page=2')
        ->assertStatus(200)
        ->assertJsonPath('meta.current_page', 2);
});

test('history never leaks another teacher records', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-27 10:00:00'));
    $a = histTeacher();
    $b = histTeacher();

    histRecord($a, Carbon::parse('2026-07-20 07:00:00'));
    histRecord($b, Carbon::parse('2026-07-21 07:00:00'));
    histRecord($b, Carbon::parse('2026-07-22 07:00:00'));

    $this->actingAs($a, 'sanctum')
        ->getJson('/api/attendance/history')
        ->assertStatus(200)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.date', '2026-07-20');
});
