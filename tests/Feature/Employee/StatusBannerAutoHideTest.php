<?php

declare(strict_types=1);

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;

function bannerEmployeeCheckedIn(AttendanceStatus $status): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );
    $user = User::factory()->create(['role_id' => $role->id]);

    Attendance::create([
        'user_id' => $user->id,
        'status' => $status,
        'image_path' => 'x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
    ]);

    return $user;
}

// The status banner is a notice, not a permanent card: it clears itself after
// 10 seconds, and a tap clears it right away.
test('the status banner dismisses itself after 10 seconds and on tap when late', function () {
    $user = bannerEmployeeCheckedIn(AttendanceStatus::Late);

    $this->actingAs($user)
        ->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('setTimeout(() => dismiss(), 10000)', false)
        ->assertSee('@click="dismiss()"', false);
});

test('the status banner dismisses itself after 10 seconds and on tap when present on time', function () {
    $user = bannerEmployeeCheckedIn(AttendanceStatus::Present);

    $this->actingAs($user)
        ->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('setTimeout(() => dismiss(), 10000)', false)
        ->assertSee('@click="dismiss()"', false);
});

// Leaving beranda and coming back must not bring it back, so the dismissal is
// remembered against today's attendance record. Tomorrow's check-in is a new
// record, so the banner shows once again.
test('the status banner remembers its dismissal against the attendance record', function () {
    $user = bannerEmployeeCheckedIn(AttendanceStatus::Late);
    $id = Attendance::where('user_id', $user->id)->value('id');

    $this->actingAs($user)
        ->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee("localStorage.getItem('statusBannerSeen') !== '{$id}'", false)
        ->assertSee("localStorage.setItem('statusBannerSeen', '{$id}')", false);
});
