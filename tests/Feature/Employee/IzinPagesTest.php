<?php

// tests/Feature/Employee/IzinPagesTest.php

use App\Models\Leave;
use App\Models\Role;
use App\Models\User;

function izinUser(string $slug = 'guru'): User
{
    $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug), 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

function izinLeave(User $user, string $status, array $extra = []): Leave
{
    return Leave::create(array_merge(['user_id' => $user->id, 'type' => 'sakit', 'start_date' => '2026-07-21', 'end_date' => '2026-07-22', 'reason' => 'Demam tinggi', 'status' => $status], $extra));
}

test('leave list shows type, duration and a status chip', function () {
    $user = izinUser();
    izinLeave($user, 'pending');
    izinLeave($user, 'rejected', ['reason' => 'Acara keluarga']);

    $this->actingAs($user)->get(route('attendance.leaves.index'))
        ->assertSuccessful()
        ->assertSee('Sakit · 2 hari')
        ->assertSee('g-chip g-chip--pending', false)
        ->assertSee('g-chip g-chip--attn', false)
        ->assertSee('href="'.route('attendance.leaves.create').'"', false)
        ->assertDontSee('glass-card');
});

test('empty leave list teaches how to apply', function () {
    $this->actingAs(izinUser())->get(route('attendance.leaves.index'))
        ->assertSee('Belum ada pengajuan')
        ->assertSee('Ajukan Izin');
});

test('leave form offers three choice cards and an accessible attachment', function () {
    $html = $this->actingAs(izinUser())->get(route('attendance.leaves.create'))
        ->assertSee('Jenis Perizinan')
        ->assertSee('id="attachment"', false)
        ->assertSee('Kirim Pengajuan Izin')
        ->getContent();

    expect(substr_count($html, 'class="g-choice"'))->toBe(3)
        ->and(substr_count($html, 'name="type"'))->toBe(3);
});

test('leave detail shows the rejection reason', function () {
    $user = izinUser();
    $leave = izinLeave($user, 'rejected', ['rejection_reason' => 'Dokumen kurang']);

    $this->actingAs($user)->get(route('attendance.leaves.show', $leave))
        ->assertSee('Demam tinggi')
        ->assertSee('Dokumen kurang');
});

test('approval pages filter and act on requests', function () {
    $head = izinUser('kepala-sekolah');
    $leave = izinLeave(izinUser(), 'pending');

    $this->actingAs($head)->get(route('approval.leaves.index', ['status' => 'pending']))
        ->assertSuccessful()
        ->assertSee('aria-current="page"', false)
        ->assertSee('1 menunggu')
        ->assertSee(route('approval.leaves.show', $leave));

    $this->actingAs($head)->get(route('approval.leaves.show', $leave))
        ->assertSee('Setujui Pengajuan')
        ->assertSee('name="rejection_reason"', false)
        ->assertSee('Tolak Pengajuan');
});
