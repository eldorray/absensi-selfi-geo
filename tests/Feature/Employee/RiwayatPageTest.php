<?php

// tests/Feature/Employee/RiwayatPageTest.php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

test('history groups days per month with times and status chips', function () {
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $user = User::factory()->create(['role_id' => $role->id]);
    foreach ([['2026-07-20 06:52:00', AttendanceStatus::Present, '2026-07-20 14:05:00'], ['2026-06-30 07:31:00', AttendanceStatus::Late, null]] as [$at, $status, $out]) {
        $a = Attendance::create(['user_id' => $user->id, 'status' => $status, 'image_path' => 'x.jpg', 'check_in_lat' => -6.2, 'check_in_long' => 106.8, 'distance_meters' => 38, 'check_out_at' => $out]);
        $a->created_at = Carbon::parse($at);
        $a->save();
    }

    $this->actingAs($user)->get(route('attendance.index'))
        ->assertSuccessful()
        ->assertSeeInOrder(['Juli 2026', '06.52', '14.05', 'Tepat waktu', 'Juni 2026', '07.31', '––.––', 'Terlambat'])
        ->assertSee('38 m dari titik absen')
        ->assertDontSee('glass-card');
});

test('empty history teaches the first step', function () {
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    $this->actingAs(User::factory()->create(['role_id' => $role->id]))->get(route('attendance.index'))
        ->assertSee('Belum ada riwayat')
        ->assertSee('href="'.route('attendance.dashboard').'"', false);
});
