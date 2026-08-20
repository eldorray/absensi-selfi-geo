<?php

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\Office;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;

function homeroomAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

function homeroomTeacher(string $level, string $name = 'Guru Wali'): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create(['name' => 'Kantor '.strtoupper($level), 'school_level' => $level, 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id, 'office_id' => $office->id]);
}

function homeroomYear(string $name = '2026/2027', bool $active = true): AcademicYear
{
    return AcademicYear::create(['name' => $name, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => $active]);
}

function homeroomClass(string $level = 'mi', string $name = 'Kelas 1A'): SchoolClass
{
    return SchoolClass::create(['school_level' => $level, 'name' => $name, 'normalized_name' => SchoolClass::normalizeName($name), 'grade_level' => 1, 'is_active' => true]);
}

test('saving the edit class form also persists its homeroom teacher', function () {
    $year = homeroomYear();
    $class = homeroomClass();
    $teacher = homeroomTeacher('mi');

    $this->actingAs(homeroomAdmin())->put(route('admin.school-classes.update', ['schoolLevel' => 'mi', 'schoolClass' => $class]), [
        'name' => $class->name,
        'grade_level' => $class->grade_level,
        'is_active' => '1',
        'teacher_id' => $teacher->id,
    ])->assertRedirect(route('admin.school-classes.index', 'mi'));

    $this->assertDatabaseHas('homeroom_assignments', [
        'academic_year_id' => $year->id,
        'school_class_id' => $class->id,
        'teacher_id' => $teacher->id,
    ]);
});

test('admin assigns one valid teacher to a class for an academic year', function () {
    $year = homeroomYear();
    $class = homeroomClass();
    $teacher = homeroomTeacher('mi');

    $this->actingAs(homeroomAdmin())->post(route('admin.homeroom-assignments.store'), [
        'academic_year_id' => $year->id,
        'school_class_id' => $class->id,
        'teacher_id' => $teacher->id,
    ])->assertRedirect();

    $this->assertDatabaseHas('homeroom_assignments', ['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);
});

test('teacher and class cannot be assigned twice in the same academic year', function () {
    $year = homeroomYear();
    $class = homeroomClass();
    $otherClass = homeroomClass('mi', 'Kelas 1B');
    $teacher = homeroomTeacher('mi');
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    $this->actingAs(homeroomAdmin())->post(route('admin.homeroom-assignments.store'), ['academic_year_id' => $year->id, 'school_class_id' => $otherClass->id, 'teacher_id' => $teacher->id])
        ->assertSessionHasErrors('teacher_id');
});

test('cross level teacher assignment is rejected', function () {
    $year = homeroomYear();
    $class = homeroomClass('mi');
    $teacher = homeroomTeacher('smp');

    $this->actingAs(homeroomAdmin())->post(route('admin.homeroom-assignments.store'), ['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id])
        ->assertSessionHasErrors('teacher_id');
});

test('valid assignments can be copied from the previous academic year without overwriting conflicts', function () {
    $previous = homeroomYear('2025/2026', false);
    $previous->update(['start_date' => '2025-07-01', 'end_date' => '2026-06-30']);
    $target = homeroomYear('2026/2027');
    $class = homeroomClass();
    $teacher = homeroomTeacher('mi');
    HomeroomAssignment::create(['academic_year_id' => $previous->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    $this->actingAs(homeroomAdmin())->post(route('admin.homeroom-assignments.copy-previous'), ['academic_year_id' => $target->id])
        ->assertRedirect()->assertSessionHas('success');

    $this->assertDatabaseHas('homeroom_assignments', ['academic_year_id' => $target->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);
});

test('copy preview separates valid assignments before applying them', function () {
    $previous = homeroomYear('2025/2026', false);
    $previous->update(['start_date' => '2025-07-01', 'end_date' => '2026-06-30']);
    $target = homeroomYear('2026/2027');
    $class = homeroomClass();
    $teacher = homeroomTeacher('mi');
    HomeroomAssignment::create(['academic_year_id' => $previous->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    $this->actingAs(homeroomAdmin())->get(route('admin.homeroom-assignments.index', [
        'academic_year_id' => $target->id,
        'school_level' => 'mi',
        'preview_copy' => 1,
    ]))->assertSuccessful()->assertSee('Pratinjau Penyalinan')->assertSee('Siap disalin');

    $this->assertDatabaseMissing('homeroom_assignments', ['academic_year_id' => $target->id]);
});

test('admin sees homeroom management page', function () {
    homeroomYear();
    homeroomClass();

    $this->actingAs(homeroomAdmin())->get(route('admin.homeroom-assignments.index'))
        ->assertSuccessful()->assertSee('Penugasan Wali Kelas')->assertSee('Kelas 1A');
});
