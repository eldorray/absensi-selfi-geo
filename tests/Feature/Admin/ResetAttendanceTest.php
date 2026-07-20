<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

function resetAdmin(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'administrator'],
        ['name' => 'Administrator', 'is_admin' => true],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

function resetGuru(string $name): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );

    return User::factory()->create(['name' => $name, 'role_id' => $role->id]);
}

function todayAttendanceFor(User $user): Attendance
{
    $att = Attendance::create([
        'user_id' => $user->id,
        'status' => AttendanceStatus::Present,
        'image_path' => 'attendance/in.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 10,
        'check_out_at' => Carbon::today()->setTime(16, 0),
        'check_out_image_path' => 'attendance/out.jpg',
    ]);
    $att->created_at = Carbon::today()->setTime(7, 0);
    $att->save();

    return $att;
}

test('daily report shows a reset button for a guru who has checked in', function () {
    $guru = resetGuru('Guru Hadir');
    $att = todayAttendanceFor($guru);

    $this->actingAs(resetAdmin())
        ->get(route('admin.reports.daily', ['date' => Carbon::today()->format('Y-m-d')]))
        ->assertStatus(200)
        ->assertSee(route('admin.reports.daily.reset', $att), false);
});

test('daily report shows no reset button for a guru who has not checked in', function () {
    resetGuru('Guru Absen');

    $this->actingAs(resetAdmin())
        ->get(route('admin.reports.daily', ['date' => Carbon::today()->format('Y-m-d')]))
        ->assertStatus(200)
        ->assertDontSee('reports/daily/reset');
});

test('admin can reset a guru attendance, deleting the record and its images', function () {
    Storage::fake('public');
    Storage::disk('public')->put('attendance/in.jpg', 'x');
    Storage::disk('public')->put('attendance/out.jpg', 'y');

    $guru = resetGuru('Guru Hadir');
    $att = todayAttendanceFor($guru);

    $this->actingAs(resetAdmin())
        ->delete(route('admin.reports.daily.reset', $att))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Attendance::find($att->id))->toBeNull();
    Storage::disk('public')->assertMissing('attendance/in.jpg');
    Storage::disk('public')->assertMissing('attendance/out.jpg');
});

test('non-admin cannot reset an attendance', function () {
    $guru = resetGuru('Guru Hadir');
    $att = todayAttendanceFor($guru);

    $employee = User::factory()->create([
        'role_id' => Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false])->id,
    ]);

    $this->actingAs($employee)
        ->delete(route('admin.reports.daily.reset', $att))
        ->assertRedirect(route('attendance.dashboard'));

    expect(Attendance::find($att->id))->not->toBeNull();
});
