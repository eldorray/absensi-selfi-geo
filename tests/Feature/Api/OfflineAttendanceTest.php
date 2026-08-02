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

test('client_uuid without captured_at is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'client_uuid' => '77777777-7777-4777-8777-777777777777',
    ])->assertStatus(422)->assertJsonValidationErrors('captured_at');

    expect(Attendance::count())->toBe(0);

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

test('yesterday\'s client_uuid replayed today is rejected, not resolved to the stale record', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 07:00:00'));
    $user = offlineTeacher();
    $clientUuid = '88888888-8888-4888-8888-888888888888';

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 07:00:00',
        'client_uuid' => $clientUuid,
    ])->assertStatus(201);

    Carbon::setTestNow(Carbon::parse('2026-08-04 07:00:00'));

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'client_uuid' => $clientUuid,
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('captured_at');
    expect(Attendance::count())->toBe(1)
        ->and(Attendance::query()->whereDate('created_at', today())->count())->toBe(0);

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

test('a queued check-out is stored at its capture time and is idempotent', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 18:00:00'));
    $user = offlineTeacher();

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
    ]);
    $attendance->created_at = Carbon::parse('2026-08-03 07:00:00');
    $attendance->save();

    $payload = [
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 15:35:00',
        'client_uuid' => '77777777-7777-4777-8777-777777777777',
    ];

    $first = $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', $payload);
    $second = $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', $payload);

    $first->assertStatus(200)->assertJsonPath('check_out_time', '15:35');
    $second->assertStatus(200)->assertJsonPath('check_out_time', '15:35');

    $fresh = $attendance->fresh();
    expect($fresh->check_out_at->format('H:i'))->toBe('15:35')
        ->and($fresh->check_out_synced_at->format('H:i'))->toBe('18:00');

    Carbon::setTestNow();
});

test('a queued check-out captured before the window opens is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 18:00:00'));
    $user = offlineTeacher();

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
    ]);
    $attendance->created_at = Carbon::parse('2026-08-03 07:00:00');
    $attendance->save();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', [
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 15:00:00',
        'client_uuid' => '99999999-9999-4999-8999-999999999999',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('time');

    expect($attendance->fresh()->check_out_at)->toBeNull();

    Carbon::setTestNow();
});

/*
 * Both clients put captured_at on the wire as ISO8601 with an explicit UTC
 * offset — iOS via ISO8601DateFormatter(.withInternetDateTime), Android via
 * Instant.toString(). Every other test in this file sends an offset-less
 * string, which Carbon parses in the app timezone, so none of them exercised
 * the format the app actually sends.
 */

test('a queued check-in sent as UTC is stored at the local wall-clock time', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 10:00:00'));
    $user = offlineTeacher();

    // 06:30 Asia/Jakarta == 23:30Z the previous day. An ordinary early arrival:
    // before_check_in is 60 minutes, so the window opened at 06:00.
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-02T23:30:00Z',
        'client_uuid' => '88888888-8888-4888-8888-888888888888',
    ]);

    $response->assertStatus(201)->assertJsonPath('check_in_time', '06:30');

    // The date matters as much as the time: stored a day early, the record
    // leaves the teacher absent today and phantom-present yesterday.
    expect(Attendance::first()->created_at->format('Y-m-d H:i'))->toBe('2026-08-03 06:30');

    Carbon::setTestNow();
});

test('replaying a UTC captured_at from before local midnight still resolves to the stored row', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 10:00:00'));
    $user = offlineTeacher();

    $payload = [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-02T23:30:00Z',
        'client_uuid' => '99999999-9999-4999-8999-999999999999',
    ];

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', $payload)->assertStatus(201);
    $replay = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', $payload);

    // The idempotency lookup is scoped with whereDate('created_at', today());
    // a row written a day early is invisible to it, and the replay would then
    // create a second attendance for the same shutter press.
    $replay->assertStatus(200)->assertJsonPath('check_in_time', '06:30');
    expect(Attendance::count())->toBe(1);

    Carbon::setTestNow();
});

test('a queued check-out sent as UTC is stored at the local wall-clock time', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 20:00:00'));
    $user = offlineTeacher();

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
    ]);
    $attendance->created_at = Carbon::parse('2026-08-03 07:00:00');
    $attendance->save();

    // 16:05 Asia/Jakarta == 09:05Z the same day.
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', [
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03T09:05:00Z',
        'client_uuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    ]);

    $response->assertStatus(200)->assertJsonPath('check_out_time', '16:05');
    expect($attendance->fresh()->check_out_at->format('Y-m-d H:i'))->toBe('2026-08-03 16:05');

    Carbon::setTestNow();
});
