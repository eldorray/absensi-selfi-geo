<?php

use App\Models\AcademicYear;
use App\Models\BkRecord;
use App\Models\HomeroomAssignment;
use App\Models\Office;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

function myClassApiTeacher(string $level = 'mi'): User
{
    $role = Role::firstOrCreate(['slug' => 'guru-api'], ['name' => 'Guru API', 'is_admin' => false]);
    $office = Office::create(['name' => 'Unit '.uniqid(), 'school_level' => $level, 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);

    return User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
}

function myClassApiAssign(User $teacher, string $level = 'mi'): SchoolClass
{
    $year = AcademicYear::firstOrCreate(['name' => '2026/2027'], ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $name = 'Kelas '.uniqid();
    $class = SchoolClass::create(['school_level' => $level, 'name' => $name, 'normalized_name' => SchoolClass::normalizeName($name), 'grade_level' => 1, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    return $class;
}

function myClassApiStudent(SchoolClass $class, string $name): Student
{
    return Student::create(['school_class_id' => $class->id, 'school_level' => $class->school_level, 'source' => 'manual', 'nama_lengkap' => $name, 'nisn' => fake()->unique()->numerify('##########'), 'status' => 'Aktif']);
}

test('kelas saya menolak tamu dan guru tanpa penugasan aktif', function () {
    $this->getJson('/api/my-class')->assertUnauthorized();
    $this->actingAs(myClassApiTeacher(), 'sanctum')->getJson('/api/my-class')->assertForbidden();
});

test('wali kelas hanya melihat siswa kelasnya beserta hitungan pelanggaran', function () {
    $teacher = myClassApiTeacher();
    $class = myClassApiAssign($teacher);
    $mine = myClassApiStudent($class, 'Siswa Kelas Saya');
    $other = myClassApiStudent(myClassApiAssign(myClassApiTeacher()), 'Siswa Kelas Lain');

    BkRecord::create(['counselor_id' => myClassApiTeacher()->id, 'student_id' => $mine->id, 'school_level' => 'mi', 'record_type' => 'violation', 'occurred_at' => now(), 'status' => 'new']);

    $response = $this->actingAs($teacher, 'sanctum')->getJson('/api/my-class')->assertOk();

    $response->assertJsonPath('assignment.class_name', $class->name)
        ->assertJsonPath('summary.student_count', 1)
        ->assertJsonPath('summary.students_with_violations', 1)
        ->assertJsonPath('data.0.nama_lengkap', 'Siswa Kelas Saya')
        ->assertJsonPath('data.0.violations_count', 1)
        ->assertJsonMissing(['nama_lengkap' => 'Siswa Kelas Lain']);

    $this->getJson("/api/my-class/students/{$other->id}")->assertForbidden();
});

test('detail siswa wali kelas mengirim pelanggaran tanpa isi konseling', function () {
    $teacher = myClassApiTeacher();
    $class = myClassApiAssign($teacher);
    $student = myClassApiStudent($class, 'Siswa Dengan Catatan');
    $counselor = myClassApiTeacher();

    BkRecord::create(['counselor_id' => $counselor->id, 'student_id' => $student->id, 'school_level' => 'mi', 'record_type' => 'violation', 'occurred_at' => now(), 'severity' => 'light', 'chronology' => 'Kronologi rahasia', 'action_taken' => 'Teguran internal', 'status' => 'new']);
    BkRecord::create(['counselor_id' => $counselor->id, 'student_id' => $student->id, 'school_level' => 'mi', 'record_type' => 'counseling', 'occurred_at' => now(), 'counseling_content' => 'Isi konseling rahasia', 'counseling_result' => 'Hasil rahasia', 'status' => 'new']);

    $response = $this->actingAs($teacher, 'sanctum')->getJson("/api/my-class/students/{$student->id}")->assertOk();

    expect($response->json('violations'))->toHaveCount(1);
    $response->assertJsonPath('student.nama_lengkap', 'Siswa Dengan Catatan')
        ->assertJsonPath('violations.0.severity', 'light');

    $body = $response->getContent();
    expect($body)->not->toContain('Kronologi rahasia')
        ->and($body)->not->toContain('Teguran internal')
        ->and($body)->not->toContain('Isi konseling rahasia')
        ->and($body)->not->toContain('Hasil rahasia');
});

test('ringkasan bk wali kelas hanya berisi hitungan dan jenis', function () {
    $teacher = myClassApiTeacher();
    $class = myClassApiAssign($teacher);
    $student = myClassApiStudent($class, 'Siswa Ringkasan');

    BkRecord::create(['counselor_id' => myClassApiTeacher()->id, 'student_id' => $student->id, 'school_level' => 'mi', 'record_type' => 'counseling', 'occurred_at' => now(), 'counseling_content' => 'Rahasia', 'status' => 'in_progress']);

    $this->actingAs($teacher, 'sanctum')->getJson("/api/my-class/students/{$student->id}/bk-summary")
        ->assertOk()
        ->assertJsonPath('active_count', 1)
        ->assertJsonPath('types', ['counseling'])
        ->assertJsonPath('needs_follow_up', true)
        ->assertJsonMissing(['counseling_content' => 'Rahasia']);
});

test('dashboard mengirim kapabilitas dan ringkasan kelas wali', function () {
    $teacher = myClassApiTeacher();
    $class = myClassApiAssign($teacher);
    myClassApiStudent($class, 'Siswa Dashboard');

    $this->actingAs($teacher, 'sanctum')->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('capabilities.is_homeroom_teacher', true)
        ->assertJsonPath('capabilities.can_access_bk', false)
        ->assertJsonPath('capabilities.can_approve_leave', false)
        ->assertJsonPath('homeroom.class_name', $class->name)
        ->assertJsonPath('homeroom.student_count', 1);

    $this->actingAs(myClassApiTeacher(), 'sanctum')->getJson('/api/dashboard')
        ->assertOk()
        ->assertJsonPath('capabilities.is_homeroom_teacher', false)
        ->assertJsonPath('homeroom', null);
});
