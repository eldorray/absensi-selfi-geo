<?php

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\Office;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function studentAffairsTeacher(array $overrides = []): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::query()->create([
        'name' => 'Unit Kesiswaan '.uniqid(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
        'school_level' => 'mi',
    ]);

    return User::factory()->create(array_merge([
        'role_id' => $role->id,
        'office_id' => $office->id,
        'is_bk_counselor' => false,
        'is_student_affairs_officer' => false,
    ], $overrides));
}

function assignHomeroom(User $teacher): Student
{
    $year = AcademicYear::query()->create([
        'name' => '2026/2027',
        'start_date' => '2026-07-01',
        'end_date' => '2027-06-30',
        'is_active' => true,
    ]);
    $class = SchoolClass::query()->create([
        'name' => '6 MI A',
        'normalized_name' => '6 mi a',
        'school_level' => 'mi',
        'grade_level' => 6,
        'is_active' => true,
    ]);
    HomeroomAssignment::query()->create([
        'academic_year_id' => $year->id,
        'school_class_id' => $class->id,
        'teacher_id' => $teacher->id,
        'assigned_by' => $teacher->id,
    ]);

    return Student::factory()->create([
        'school_class_id' => $class->id,
        'school_level' => 'mi',
        'status' => 'Aktif',
    ]);
}

test('only appointed student affairs officer may open full student affairs workspace', function () {
    $ordinary = studentAffairsTeacher();
    $homeroom = studentAffairsTeacher();
    assignHomeroom($homeroom);
    $bk = studentAffairsTeacher(['is_bk_counselor' => true]);
    $officer = studentAffairsTeacher(['is_student_affairs_officer' => true]);

    $this->actingAs($ordinary)->get(route('attendance.kesiswaan.index'))->assertForbidden();
    $this->actingAs($homeroom)->get(route('attendance.kesiswaan.index'))->assertForbidden();
    $this->actingAs($bk)->get(route('attendance.kesiswaan.index'))->assertForbidden();
    $this->actingAs($officer)->get(route('attendance.kesiswaan.index'))->assertSuccessful();
});

test('appointed student affairs officer sees the student directory workspace', function () {
    $officer = studentAffairsTeacher(['is_student_affairs_officer' => true]);
    Student::factory()->create([
        'nama_lengkap' => 'Siswa Direktori',
        'school_level' => 'mi',
        'status' => 'Aktif',
    ]);

    $this->actingAs($officer)
        ->get(route('attendance.kesiswaan.index'))
        ->assertSuccessful()
        ->assertSee('Direktori siswa')
        ->assertSee('Siswa Direktori');
});

test('appointed student affairs officer can open an authorized student profile', function () {
    $officer = studentAffairsTeacher(['is_student_affairs_officer' => true]);
    $student = Student::factory()->create([
        'school_level' => 'mi',
        'status' => 'Aktif',
    ]);

    $this->actingAs($officer)
        ->get(route('attendance.kesiswaan.show', $student))
        ->assertSuccessful()
        ->assertSee($student->nama_lengkap);
});

test('student affairs officer fails closed without mapped school level', function () {
    $officer = studentAffairsTeacher(['is_student_affairs_officer' => true]);
    $officer->office->update(['school_level' => null]);

    $this->actingAs($officer)->get(route('attendance.kesiswaan.index'))->assertForbidden();
});

test('homeroom sees my referrals while student affairs officer and ordinary teacher do not', function () {
    $homeroom = studentAffairsTeacher();
    assignHomeroom($homeroom);
    $officer = studentAffairsTeacher(['is_student_affairs_officer' => true]);
    $ordinary = studentAffairsTeacher();

    $this->actingAs($homeroom)->get(route('attendance.referrals.mine'))->assertSuccessful();
    $this->actingAs($officer)->get(route('attendance.referrals.mine'))->assertForbidden();
    $this->actingAs($ordinary)->get(route('attendance.referrals.mine'))->assertForbidden();
});

test('bk counselor sees referral queue without receiving full student affairs access', function () {
    $bk = studentAffairsTeacher(['is_bk_counselor' => true]);

    $this->actingAs($bk)->get(route('attendance.referrals.queue'))->assertSuccessful();
    $this->actingAs($bk)->get(route('attendance.kesiswaan.index'))->assertForbidden();
});

test('administrator can assign student affairs capability independently from bk', function () {
    $adminRole = Role::firstOrCreate(['slug' => 'admin-kesiswaan'], ['name' => 'Admin Kesiswaan', 'is_admin' => true]);
    $teacherRole = Role::firstOrCreate(['slug' => 'guru-kesiswaan'], ['name' => 'Guru', 'is_admin' => false]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $office = Office::query()->create([
        'name' => 'Unit MI Kesiswaan',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
        'school_level' => 'mi',
    ]);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Petugas Kesiswaan',
        'email' => 'kesiswaan@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $teacherRole->id,
        'office_id' => $office->id,
        'is_student_affairs_officer' => '1',
    ])->assertRedirect(route('admin.users.index'));

    $teacher = User::query()->where('email', 'kesiswaan@example.test')->firstOrFail();
    expect($teacher->is_student_affairs_officer)->toBeTrue()
        ->and($teacher->is_bk_counselor)->toBeFalse();
});

test('employee navigation separates student affairs my referrals and bk queue', function () {
    $homeroom = studentAffairsTeacher();
    assignHomeroom($homeroom);
    $officer = studentAffairsTeacher(['is_student_affairs_officer' => true]);
    $bk = studentAffairsTeacher(['is_bk_counselor' => true]);

    $this->actingAs($homeroom)->get(route('attendance.dashboard'))->assertSee('Rujukan Saya')->assertDontSee('>Kesiswaan<', false);
    $this->actingAs($officer)->get(route('attendance.dashboard'))->assertSee('>Kesiswaan<', false)->assertDontSee('Rujukan Saya');
    $this->actingAs($bk)->get(route('attendance.dashboard'))->assertSee('Antrean Rujukan')->assertDontSee('>Kesiswaan<', false);
});
