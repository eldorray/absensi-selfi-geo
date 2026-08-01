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

test('the attendance list marks rows that arrived from the offline queue', function () {
    $admin = badgeAdmin();
    $office = Office::create([
        'name' => 'MI Badge',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);
    $teacherRole = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $teacher = User::create([
        'name' => 'Guru Badge',
        'email' => 'guru'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $teacherRole->id,
        'office_id' => $office->id,
    ]);

    Attendance::create([
        'user_id' => $teacher->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
        'synced_at' => Carbon::parse('2026-08-03 10:00:00'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.attendances.index'))
        ->assertOk()
        ->assertSee('Offline');
});
