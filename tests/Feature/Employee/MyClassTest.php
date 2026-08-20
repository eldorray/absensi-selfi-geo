<?php

use App\Models\AcademicYear;
use App\Models\BkRecord;
use App\Models\HomeroomAssignment;
use App\Models\Office;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

function myClassTeacher(string $level = 'mi'): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create(['name' => 'Unit '.strtoupper($level), 'school_level' => $level, 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);

    return User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id]);
}

function assignMyClass(User $teacher, string $className = 'Kelas 1A'): SchoolClass
{
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $class = SchoolClass::create(['school_level' => 'mi', 'name' => $className, 'normalized_name' => SchoolClass::normalizeName($className), 'grade_level' => 1, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    return $class;
}

function classStudent(SchoolClass $class, string $name): Student
{
    return Student::create(['school_class_id' => $class->id, 'school_level' => 'mi', 'source' => 'manual', 'nama_lengkap' => $name, 'nisn' => fake()->unique()->numerify('##########'), 'status' => 'Aktif']);
}

test('student detail has a back button to my class list', function () {
    $view = file_get_contents(resource_path('views/attendance/my-class/show.blade.php'));

    expect($view)->toContain('backUrl="{{ route(\'attendance.my-class.index\') }}"');
});

test('my class student list uses a compact mobile Material 3 surface', function () {
    $view = file_get_contents(resource_path('views/attendance/my-class/index.blade.php'));

    expect($view)
        ->toContain('data-my-class-list')
        ->toContain('divide-y')
        ->toContain('violations_count > 0')
        ->not->toContain('space-y-4 p-4')
        ->not->toContain('solid-panel flex min-h-16');
});

test('homeroom teacher sees only students in assigned class', function () {
    $teacher = myClassTeacher();
    $class = assignMyClass($teacher);
    classStudent($class, 'Siswa Kelas Saya');
    $otherClass = SchoolClass::create(['school_level' => 'mi', 'name' => 'Kelas 2A', 'normalized_name' => 'kelas 2a', 'grade_level' => 2, 'is_active' => true]);
    classStudent($otherClass, 'Siswa Kelas Lain');

    $this->actingAs($teacher)->get(route('attendance.my-class.index'))
        ->assertSuccessful()->assertSee('Siswa Kelas Saya')->assertDontSee('Siswa Kelas Lain');
});

test('teacher without active assignment is denied', function () {
    $this->actingAs(myClassTeacher())->get(route('attendance.my-class.index'))->assertForbidden();
});

test('homeroom teacher cannot open student from another class', function () {
    $teacher = myClassTeacher();
    assignMyClass($teacher);
    $otherClass = SchoolClass::create(['school_level' => 'mi', 'name' => 'Kelas 2A', 'normalized_name' => 'kelas 2a', 'grade_level' => 2, 'is_active' => true]);
    $student = classStudent($otherClass, 'Siswa Lain');

    $this->actingAs($teacher)->get(route('attendance.my-class.show', $student))->assertForbidden();
});

test('homeroom teacher sees violations but never counseling records', function () {
    $teacher = myClassTeacher();
    $class = assignMyClass($teacher);
    $student = classStudent($class, 'Siswa Dengan Catatan');
    $counselor = myClassTeacher();
    BkRecord::create(['counselor_id' => $counselor->id, 'student_id' => $student->id, 'school_level' => 'mi', 'record_type' => 'violation', 'occurred_at' => now(), 'severity' => 'light', 'chronology' => 'Pelanggaran terlihat', 'action_taken' => 'Teguran', 'status' => 'new']);
    BkRecord::create(['counselor_id' => $counselor->id, 'student_id' => $student->id, 'school_level' => 'mi', 'record_type' => 'counseling', 'occurred_at' => now(), 'counseling_content' => 'Rahasia konseling', 'counseling_result' => 'Rahasia hasil', 'status' => 'new']);

    $this->actingAs($teacher)->get(route('attendance.my-class.show', $student))
        ->assertSuccessful()->assertSee('Pelanggaran terlihat')->assertDontSee('Rahasia konseling')->assertDontSee('Rahasia hasil');
});
