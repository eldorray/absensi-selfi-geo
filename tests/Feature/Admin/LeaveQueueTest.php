<?php

use App\Models\AcademicYear;
use App\Models\Leave;
use App\Models\Role;
use App\Models\User;

function queueAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

function queueTeacher(string $name): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id, 'name' => $name]);
}

function queueLeave(User $user, string $status, string $type = 'izin', string $date = '2026-09-29', string $reason = 'Keperluan keluarga'): Leave
{
    return Leave::create(['user_id' => $user->id, 'type' => $type, 'start_date' => $date, 'end_date' => $date, 'reason' => $reason, 'status' => $status]);
}

test('the queue opens on pending requests and shows the newest one in detail', function () {
    $dewi = queueTeacher('Dewi Lestari');
    queueLeave(queueTeacher('Hendra Wijaya'), 'rejected', reason: 'Sudah ditolak');
    queueLeave($dewi, 'pending', 'sakit', reason: 'Demam dan radang tenggorokan');

    $this->actingAs(queueAdmin())
        ->get(route('admin.leaves.index'))
        ->assertOk()
        ->assertSee('Dewi Lestari')
        ->assertDontSee('Hendra Wijaya')
        ->assertSee('Demam dan radang tenggorokan')
        ->assertSee('aria-current="true"', false)
        ->assertSee(route('admin.leaves.approve', Leave::where('user_id', $dewi->id)->first()), false)
        ->assertSee('Alasan penolakan');
});

test('status=all lists every request and a chosen request opens in the detail panel', function () {
    $hendra = queueTeacher('Hendra Wijaya');
    $rejected = queueLeave($hendra, 'rejected', reason: 'Keperluan keluarga');
    $rejected->update(['rejection_reason' => 'Bertepatan dengan asesmen.']);
    queueLeave(queueTeacher('Nur Aini'), 'pending');

    $this->actingAs(queueAdmin())
        ->get(route('admin.leaves.index', ['status' => 'all', 'leave' => $rejected->id]))
        ->assertOk()
        ->assertSee('Nur Aini')
        ->assertSee('Hendra Wijaya')
        ->assertSee('Bertepatan dengan asesmen.')
        ->assertDontSee('Alasan penolakan <span', false);
});

test('the detail shows approved leave days in the active academic year', function () {
    AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $ahmad = queueTeacher('Ahmad Fauzi');
    Leave::create(['user_id' => $ahmad->id, 'type' => 'izin', 'start_date' => '2026-08-03', 'end_date' => '2026-08-04', 'reason' => 'A', 'status' => 'approved']);
    Leave::create(['user_id' => $ahmad->id, 'type' => 'sakit', 'start_date' => '2026-06-10', 'end_date' => '2026-06-10', 'reason' => 'Tahun lalu', 'status' => 'approved']);
    $pending = queueLeave($ahmad, 'pending');

    $response = $this->actingAs(queueAdmin())->get(route('admin.leaves.index', ['leave' => $pending->id]));

    $response->assertOk()->assertViewHas('history', ['izin' => 2, 'sakit' => 0, 'cuti' => 0]);
});

test('unknown filters fall back to the pending queue', function () {
    queueLeave(queueTeacher('Nur Aini'), 'pending');

    $this->actingAs(queueAdmin())
        ->get(route('admin.leaves.index', ['status' => 'bogus', 'type' => 'bogus']))
        ->assertOk()
        ->assertViewHas('status', 'pending')
        ->assertViewHas('type', null)
        ->assertSee('Nur Aini');
});

test('rejecting still requires a reason', function () {
    $leave = queueLeave(queueTeacher('Nur Aini'), 'pending');

    $this->actingAs(queueAdmin())
        ->post(route('admin.leaves.reject', $leave), ['rejection_reason' => ''])
        ->assertSessionHasErrors('rejection_reason');

    expect($leave->fresh()->status)->toBe('pending');
});
