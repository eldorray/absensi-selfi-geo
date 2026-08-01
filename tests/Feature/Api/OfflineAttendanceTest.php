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
