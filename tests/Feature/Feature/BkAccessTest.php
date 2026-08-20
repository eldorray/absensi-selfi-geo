<?php

use App\Models\Office;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;

function bkTeacher(bool $enabled, string $level = 'mi'): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::query()->create([
        'name' => 'Unit '.strtoupper($level).' '.uniqid(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
        'school_level' => $level,
    ]);

    return User::factory()->create([
        'role_id' => $role->id,
        'office_id' => $office->id,
        'is_bk_counselor' => $enabled,
    ]);
}

test('only enabled bk teachers with configured school level may access bk routes', function () {
    $regular = bkTeacher(false);
    $bk = bkTeacher(true);
    $unconfigured = bkTeacher(true);
    $unconfigured->office->update(['school_level' => null]);

    $this->actingAs($regular)->get(route('attendance.bk.index'))->assertForbidden();
    $this->actingAs($unconfigured)->get(route('attendance.bk.index'))->assertForbidden();
    $this->actingAs($bk)->get(route('attendance.bk.index'))->assertSuccessful();
});

test('bk dashboard shortcut only appears for enabled counselor', function () {
    $regular = bkTeacher(false);
    $bk = bkTeacher(true);

    $this->actingAs($regular)->get(route('attendance.dashboard'))->assertDontSee('>BK<', false);
    $this->actingAs($bk)->get(route('attendance.dashboard'))->assertSee('>BK<', false);
});

test('bk teacher only sees students from their office school level', function () {
    $bk = bkTeacher(true, 'mi');
    Student::factory()->create(['school_level' => 'mi', 'nama_lengkap' => 'Siswa Unit MI']);
    Student::factory()->create(['school_level' => 'smp', 'nama_lengkap' => 'Siswa Unit SMP']);

    $this->actingAs($bk)->get(route('attendance.bk.create'))
        ->assertSuccessful()
        ->assertSee('Siswa Unit MI')
        ->assertDontSee('Siswa Unit SMP');
});

test('bk student fields render accessible live-search comboboxes', function () {
    $bk = bkTeacher(true, 'mi');
    Student::factory()->create([
        'school_level' => 'mi',
        'nama_lengkap' => 'Ahmad Pencarian',
        'nisn' => '1234567890',
        'nik' => '3201000000000001',
    ]);

    $this->actingAs($bk)->get(route('attendance.bk.create'))
        ->assertSuccessful()
        ->assertSee('data-bk-student-combobox="primary"', false)
        ->assertSee('role="combobox"', false)
        ->assertSee('aria-controls="primary-student-options"', false)
        ->assertSee('name="student_id"', false)
        ->assertSee('data-bk-student-combobox="related"', false)
        ->assertSee('name="related_student_ids[]"', false)
        ->assertSee('Ahmad Pencarian')
        ->assertSee('1234567890');
});

test('bk validation messages are returned in Indonesian', function () {
    $bk = bkTeacher(true, 'mi');
    $student = Student::factory()->create(['school_level' => 'mi']);

    $this->actingAs($bk)->from(route('attendance.bk.create'))->post(route('attendance.bk.store'), [
        'student_id' => $student->id,
        'record_type' => 'violation',
        'occurred_at' => now()->format('Y-m-d H:i:s'),
        'severity' => 'light',
        'custom_topic' => 'Pelanggaran lain',
    ])->assertRedirect(route('attendance.bk.create'))
        ->assertSessionHasErrors([
            'chronology' => 'Kronologi wajib diisi untuk catatan pelanggaran.',
            'action_taken' => 'Tindakan yang dilakukan wajib diisi untuk catatan pelanggaran.',
        ]);
});

test('administrator can assign bk capability when creating a teacher', function () {
    $adminRole = Role::firstOrCreate(['slug' => 'bk-admin'], ['name' => 'BK Admin', 'is_admin' => true]);
    $teacherRole = Role::firstOrCreate(['slug' => 'bk-teacher-role'], ['name' => 'BK Teacher Role', 'is_admin' => false]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $office = Office::query()->create([
        'name' => 'Unit MI Admin',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
        'school_level' => 'mi',
    ]);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Guru BK Baru',
        'email' => 'guru-bk-baru@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role_id' => $teacherRole->id,
        'office_id' => $office->id,
        'is_bk_counselor' => '1',
    ])->assertRedirect(route('admin.users.index'));

    expect(User::query()->where('email', 'guru-bk-baru@example.test')->firstOrFail()->is_bk_counselor)->toBeTrue();
});

test('bk api rejects ordinary teachers and exposes metadata to counselors', function () {
    $regular = bkTeacher(false);
    $bk = bkTeacher(true, 'smp');

    $this->actingAs($regular, 'sanctum')->getJson('/api/bk/meta')->assertForbidden();
    $this->actingAs($bk, 'sanctum')->getJson('/api/bk/meta')
        ->assertSuccessful()
        ->assertJsonPath('school_level', 'smp')
        ->assertJsonPath('limits.max_attachments', 5);
});
