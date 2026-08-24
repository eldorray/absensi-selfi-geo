<?php

use App\Models\Leave;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;

function approvalApiUser(string $slug, bool $isAdmin = false): User
{
    $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug), 'is_admin' => $isAdmin]);
    $office = Office::create(['name' => 'Unit '.uniqid(), 'school_level' => 'mi', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);

    return User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
}

function approvalApiLeave(User $applicant, string $status = 'pending'): Leave
{
    return Leave::create([
        'user_id' => $applicant->id,
        'type' => 'izin',
        'start_date' => today(),
        'end_date' => today()->addDay(),
        'reason' => 'Keperluan keluarga',
        'status' => $status,
    ]);
}

test('hanya admin atau kepala sekolah yang boleh membuka antrean persetujuan', function () {
    $this->getJson('/api/approval/leaves')->assertUnauthorized();

    $this->actingAs(approvalApiUser('guru-approval-api'), 'sanctum')
        ->getJson('/api/approval/leaves')->assertForbidden();

    $this->actingAs(approvalApiUser('kepala-sekolah'), 'sanctum')
        ->getJson('/api/approval/leaves')->assertOk();

    $this->actingAs(approvalApiUser('admin-approval-api', true), 'sanctum')
        ->getJson('/api/approval/leaves')->assertOk();
});

test('daftar persetujuan memuat identitas pemohon dan hitungan menunggu', function () {
    $applicant = approvalApiUser('guru-approval-api');
    $leave = approvalApiLeave($applicant);
    approvalApiLeave($applicant, 'approved');

    $this->actingAs(approvalApiUser('kepala-sekolah'), 'sanctum')
        ->getJson('/api/approval/leaves?status=pending')
        ->assertOk()
        ->assertJsonPath('pending_count', 1)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $leave->id)
        ->assertJsonPath('data.0.applicant_name', $applicant->name)
        ->assertJsonPath('data.0.type_label', 'Izin')
        ->assertJsonPath('data.0.status_label', 'Menunggu');
});

test('menyetujui mencatat penyetuju dan menolak pemrosesan ganda', function () {
    $approver = approvalApiUser('kepala-sekolah');
    $leave = approvalApiLeave(approvalApiUser('guru-approval-api'));

    $this->actingAs($approver, 'sanctum')
        ->postJson("/api/approval/leaves/{$leave->id}/approve")
        ->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.status_label', 'Disetujui');

    expect($leave->fresh()->approved_by)->toBe($approver->id)
        ->and($leave->fresh()->approved_at)->not->toBeNull();

    $this->postJson("/api/approval/leaves/{$leave->id}/approve")->assertStatus(422);
});

test('penolakan wajib menyertakan alasan berbahasa Indonesia', function () {
    $approver = approvalApiUser('kepala-sekolah');
    $leave = approvalApiLeave(approvalApiUser('guru-approval-api'));

    $this->actingAs($approver, 'sanctum')
        ->postJson("/api/approval/leaves/{$leave->id}/reject", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors('rejection_reason')
        ->assertJsonPath('errors.rejection_reason.0', 'Alasan penolakan harus diisi.');

    $this->postJson("/api/approval/leaves/{$leave->id}/reject", ['rejection_reason' => 'Kuota izin habis.'])
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected')
        ->assertJsonPath('data.rejection_reason', 'Kuota izin habis.');
});

test('detail persetujuan memuat kantor dan peran pemohon', function () {
    $applicant = approvalApiUser('guru-approval-api');
    $leave = approvalApiLeave($applicant);

    $this->actingAs(approvalApiUser('kepala-sekolah'), 'sanctum')
        ->getJson("/api/approval/leaves/{$leave->id}")
        ->assertOk()
        ->assertJsonPath('data.applicant_name', $applicant->name)
        ->assertJsonPath('data.applicant_email', $applicant->email)
        ->assertJsonPath('data.applicant_office', $applicant->office->name)
        ->assertJsonPath('data.reason', 'Keperluan keluarga');
});

test('pemohon biasa tetap hanya melihat izinnya sendiri di jalur non-persetujuan', function () {
    $applicant = approvalApiUser('guru-approval-api');
    $foreign = approvalApiLeave(approvalApiUser('guru-approval-api'));

    $this->actingAs($applicant, 'sanctum')
        ->getJson("/api/leaves/{$foreign->id}")
        ->assertNotFound();
});
