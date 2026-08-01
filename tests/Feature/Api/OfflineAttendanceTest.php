<?php

declare(strict_types=1);

use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function offlineTeacher(?Office $office = null): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );
    $office ??= Office::create([
        'name' => 'MI Test',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);

    return User::create([
        'name' => 'Guru Offline',
        'email' => 'offline'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
        'office_id' => $office->id,
    ]);
}

test('attendance stores the offline queue columns', function () {
    $user = offlineTeacher();
    $syncedAt = Carbon::parse('2026-08-01 10:00:00');

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
        'client_uuid' => '11111111-1111-4111-8111-111111111111',
        'synced_at' => $syncedAt,
        'check_out_client_uuid' => '22222222-2222-4222-8222-222222222222',
        'check_out_synced_at' => $syncedAt,
    ])->fresh();

    expect($attendance->client_uuid)->toBe('11111111-1111-4111-8111-111111111111')
        ->and($attendance->check_out_client_uuid)->toBe('22222222-2222-4222-8222-222222222222')
        ->and($attendance->synced_at->format('Y-m-d H:i'))->toBe('2026-08-01 10:00')
        ->and($attendance->check_out_synced_at)->toBeInstanceOf(Carbon::class);
});

test('captured_at from a previous day is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-02 07:00:00',
        'client_uuid' => '33333333-3333-4333-8333-333333333333',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('captured_at');
    expect(Attendance::count())->toBe(0);

    Carbon::setTestNow();
});

test('captured_at in the future is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 08:10:00',
        'client_uuid' => '44444444-4444-4444-8444-444444444444',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('captured_at');

    Carbon::setTestNow();
});

test('captured_at without client_uuid is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 07:30:00',
    ])->assertStatus(422)->assertJsonValidationErrors('client_uuid');

    Carbon::setTestNow();
});

test('a queued check-in is stored at its capture time', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 10:00:00'));
    $user = offlineTeacher();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 07:00:00',
        'client_uuid' => '55555555-5555-4555-8555-555555555555',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('check_in_time', '07:00')
        ->assertJsonPath('status', 'on_time');

    $attendance = Attendance::first();
    expect($attendance->created_at->format('H:i'))->toBe('07:00')
        ->and($attendance->synced_at->format('H:i'))->toBe('10:00');

    Carbon::setTestNow();
});

test('replaying a client_uuid returns the stored record instead of a second row', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();
    $payload = [
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 07:00:00',
        'client_uuid' => '66666666-6666-4666-8666-666666666666',
    ];

    $first = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', $payload + [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
    ]);
    $second = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', $payload + [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
    ]);

    $first->assertStatus(201);
    $second->assertStatus(200)->assertJsonPath('check_in_time', '07:00');
    expect(Attendance::count())->toBe(1);

    Carbon::setTestNow();
});

test('an online check-in still behaves exactly as before', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 07:05:00'));
    $user = offlineTeacher();

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])->assertStatus(201)->assertJsonPath('check_in_time', '07:05');

    $attendance = Attendance::first();
    expect($attendance->synced_at)->toBeNull()
        ->and($attendance->client_uuid)->toBeNull();

    Carbon::setTestNow();
});
