<?php

// tests/Feature/Employee/KesiswaanReferralPagesTest.php
// Notifikasi, detail rujukan, dan form rujukan tetap di dalam PWA guru.

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\Office;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\User;
use App\Notifications\StudentReferralCreated;

function rpTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create(['name' => 'Unit MI '.uniqid(), 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100, 'school_level' => 'mi']);

    return User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
}

function rpHomeroomStudent(User $teacher): Student
{
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $class = SchoolClass::create(['name' => '6 MI A', 'normalized_name' => '6 mi a', 'school_level' => 'mi', 'grade_level' => 6, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id, 'assigned_by' => $teacher->id]);

    return Student::factory()->create(['school_class_id' => $class->id, 'school_level' => 'mi', 'status' => 'Aktif', 'nama_lengkap' => 'Siti Rujukan']);
}

function rpReferral(User $creator): StudentReferral
{
    $student = Student::factory()->create(['school_level' => 'mi', 'nama_lengkap' => 'Siti Rujukan']);

    return StudentReferral::create([
        'student_id' => $student->id, 'created_by' => $creator->id, 'school_level' => 'mi',
        'reason' => 'Sering melamun', 'observation' => 'Diam di kelas.', 'observed_at' => '2026-09-21',
        'urgency' => 'important', 'status' => 'new',
    ]);
}

test('the three kesiswaan pages use the teacher layout', function (string $view) {
    expect(file_get_contents(resource_path("views/attendance/kesiswaan/{$view}.blade.php")))
        ->toContain('<x-layouts.mobile')
        ->not->toContain('x-layouts.app');
})->with(['notifications', 'referral', 'referral-form']);

test('the bell opens a PWA notification list with Indonesian labels', function () {
    $teacher = rpTeacher();
    $teacher->notify(new StudentReferralCreated(rpReferral($teacher)));

    $this->actingAs($teacher)->get(route('attendance.kesiswaan.notifications.index'))
        ->assertOk()
        ->assertSee('data-teacher-ui="absenku-guru"', false)
        ->assertSee('Siti Rujukan')
        ->assertSee('Rujukan baru')
        ->assertSee('Tandai semua dibaca')
        ->assertDontSee('· new');
});

test('an empty notification list says so and hides mark-all', function () {
    $this->actingAs(rpTeacher())->get(route('attendance.kesiswaan.notifications.index'))
        ->assertOk()
        ->assertSee('Belum ada notifikasi')
        ->assertDontSee('Tandai semua dibaca');
});

test('the referral detail opened from a notification stays in the PWA', function () {
    $teacher = rpTeacher();
    $referral = rpReferral($teacher);

    $this->actingAs($teacher)->get(route('attendance.kesiswaan.referrals.show', $referral))
        ->assertOk()
        ->assertSee('data-teacher-ui="absenku-guru"', false)
        ->assertSee('Siti Rujukan')
        ->assertSee('Penting')
        ->assertSee('21 September 2026')
        ->assertDontSee('Status: new');
});

test('the referral form is a PWA form with labelled fields', function () {
    $teacher = rpTeacher();
    $student = rpHomeroomStudent($teacher);

    $this->actingAs($teacher)->get(route('attendance.kesiswaan.referrals.create', $student))
        ->assertOk()
        ->assertSee('data-teacher-ui="absenku-guru"', false)
        ->assertSee('for="reason"', false)
        ->assertSee('for="observation"', false)
        ->assertSee('for="observed_at"', false)
        ->assertSee('for="urgency"', false)
        ->assertSee('Biasa');
});

test('another teacher still cannot open the referral', function () {
    $referral = rpReferral(rpTeacher());

    $this->actingAs(rpTeacher())->get(route('attendance.kesiswaan.referrals.show', $referral))->assertForbidden();
});

test('a BK counselor sees claim and status actions in the PWA and goes back to the queue', function () {
    $counselor = rpTeacher();
    $counselor->update(['is_bk_counselor' => true]);
    $referral = rpReferral(rpTeacher());

    $this->actingAs($counselor)->get(route('attendance.kesiswaan.referrals.show', $referral))
        ->assertOk()
        ->assertSee('Ambil rujukan')
        ->assertSee('for="safe_summary"', false)
        ->assertSee('href="'.route('attendance.referrals.queue').'"', false);
});
