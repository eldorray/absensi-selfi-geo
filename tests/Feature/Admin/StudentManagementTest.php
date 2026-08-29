<?php

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentSyncService;
use Illuminate\Support\Facades\Http;

function studentAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'student-admin'], ['name' => 'Student Admin', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('student and class pages are admin only and isolated by school level', function () {
    $mi = Student::factory()->create(['school_level' => 'mi', 'nama_lengkap' => 'Siswa MI', 'nisn' => '1111111111']);
    Student::factory()->create(['school_level' => 'smp', 'nama_lengkap' => 'Siswa SMP', 'nisn' => '2222222222']);

    $this->get(route('admin.students.index', 'mi'))->assertRedirect(route('login'));
    $this->actingAs(studentAdmin())->get(route('admin.students.index', 'mi'))
        ->assertSuccessful()->assertSee('1111111111')->assertDontSee('2222222222')->assertSee('data-student-module', false);
    $this->actingAs(studentAdmin())->get(route('admin.students.edit', ['smp', $mi]))->assertNotFound();
    $this->actingAs(studentAdmin())->get(route('admin.students.index', 'invalid'))->assertNotFound();
});

test('admin can create update and delete a student', function () {
    $class = SchoolClass::factory()->create(['school_level' => 'mi']);
    $admin = studentAdmin();

    $this->actingAs($admin)->post(route('admin.students.store', 'mi'), [
        'nama_lengkap' => 'Ahmad Fauzan', 'nisn' => '1234567890', 'nik' => '',
        'school_class_id' => $class->id, 'status' => 'Aktif', 'jenis_kelamin' => 'L',
    ])->assertRedirect(route('admin.students.index', 'mi'));

    $student = Student::where('nisn', '1234567890')->firstOrFail();
    expect($student->source)->toBe('manual')->and($student->tingkat_rombel)->toBe($class->name);

    $this->actingAs($admin)->put(route('admin.students.update', ['mi', $student]), [
        'nama_lengkap' => 'Ahmad Revisi', 'nisn' => '1234567890', 'nik' => '',
        'school_class_id' => '', 'status' => 'Aktif', 'jenis_kelamin' => 'L',
    ])->assertRedirect(route('admin.students.index', 'mi'));
    expect($student->fresh()->nama_lengkap)->toBe('Ahmad Revisi')->and($student->fresh()->school_class_id)->toBeNull();

    $this->actingAs($admin)->delete(route('admin.students.destroy', ['mi', $student]))->assertSessionHas('success');
    expect(Student::find($student->id))->toBeNull();
});

test('student validation requires nisn or nik and rejects a class from another level', function () {
    $smpClass = SchoolClass::factory()->create(['school_level' => 'smp']);
    $this->actingAs(studentAdmin())->post(route('admin.students.store', 'mi'), [
        'nama_lengkap' => 'Tanpa Identitas', 'nisn' => '', 'nik' => '',
        'school_class_id' => $smpClass->id, 'status' => 'Aktif',
    ])->assertSessionHasErrors(['nisn', 'nik', 'school_class_id']);
});

test('class names are normalized per level and classes with students cannot be deleted', function () {
    $admin = studentAdmin();
    $this->actingAs($admin)->post(route('admin.school-classes.store', 'mi'), [
        'name' => '  4   A ', 'grade_level' => 4, 'is_active' => '1',
    ])->assertSessionHas('success');
    $class = SchoolClass::firstOrFail();
    expect($class->normalized_name)->toBe('4 a');

    Student::factory()->create(['school_level' => 'mi', 'school_class_id' => $class->id]);
    $this->actingAs($admin)->delete(route('admin.school-classes.destroy', ['mi', $class]))
        ->assertSessionHas('error');
    expect($class->fresh())->not->toBeNull();
});

test('sync creates classes and students then updates by nisn without deleting local data', function () {
    $manual = Student::factory()->create(['school_level' => 'mi', 'nama_lengkap' => 'Tetap Lokal']);
    Http::fake(['*/api/siswa-mi/all' => Http::sequence()
        ->push(['success' => true, 'data' => [[
            'id' => 99, 'nama_lengkap' => 'Nama Awal', 'nisn' => '9988776655', 'nik' => '3200000000000001',
            'tingkat_rombel' => '4 A', 'status' => 'Aktif', 'jenis_kelamin' => 'P',
        ]]])
        ->push(['success' => true, 'data' => [[
            'id' => 99, 'nama_lengkap' => 'Nama API Baru', 'nisn' => '9988776655', 'nik' => '3200000000000001',
            'tingkat_rombel' => '4 A', 'status' => 'Aktif', 'jenis_kelamin' => 'P',
        ]]])]);

    $first = app(StudentSyncService::class)->sync('mi');
    $second = app(StudentSyncService::class)->sync('mi');

    expect($first)->toMatchArray(['created' => 1, 'updated' => 0, 'skipped' => 0])
        ->and($second)->toMatchArray(['created' => 0, 'updated' => 1, 'skipped' => 0])
        ->and(Student::where('nisn', '9988776655')->first()->nama_lengkap)->toBe('Nama API Baru')
        ->and(SchoolClass::where('school_level', 'mi')->where('normalized_name', '4 a')->exists())->toBeTrue()
        ->and($manual->fresh())->not->toBeNull();
});

test('sync falls back to nik when nisn does not match an existing student', function () {
    $student = Student::factory()->create([
        'school_level' => 'mi',
        'nisn' => null,
        'nik' => '3200000000000099',
        'nama_lengkap' => 'Nama Lokal',
    ]);
    Http::fake(['*/api/siswa-mi/all' => Http::response(['success' => true, 'data' => [[
        'id' => 100,
        'nama_lengkap' => 'Nama dari API',
        'nisn' => '1122334455',
        'nik' => '3200000000000099',
        'tingkat_rombel' => '5 B',
        'status' => 'Aktif',
    ]]])]);

    $result = app(StudentSyncService::class)->sync('mi');

    expect($result)->toMatchArray(['created' => 0, 'updated' => 1, 'skipped' => 0])
        ->and(Student::count())->toBe(1)
        ->and($student->fresh()->nisn)->toBe('1122334455')
        ->and($student->fresh()->nama_lengkap)->toBe('Nama dari API');
});

test('sync skips rows without identifiers and rejects malformed responses atomically', function () {
    Http::fake(['*/api/siswa-smp/all' => Http::response(['success' => true, 'data' => [['nama_lengkap' => 'Anonim']]])]);
    expect(app(StudentSyncService::class)->sync('smp')['skipped'])->toBe(1)->and(Student::count())->toBe(0);

    Http::fake(['*/api/siswa-mi/all' => Http::response(['success' => true, 'data' => 'invalid'])]);
    expect(fn () => app(StudentSyncService::class)->sync('mi'))->toThrow(RuntimeException::class);
    expect(Student::count())->toBe(0);
});

test('sync reassigns an external_id already held by a different student', function () {
    $stale = Student::factory()->create([
        'school_level' => 'smp', 'external_id' => '7', 'nisn' => '9999999999', 'nama_lengkap' => 'Siswa Lama',
    ]);

    Http::fake(['*/api/siswa-smp/all' => Http::response(['success' => true, 'data' => [
        ['id' => 7, 'nisn' => '1234512345', 'nama_lengkap' => 'Siswa Baru'],
    ]])]);

    expect(app(StudentSyncService::class)->sync('smp'))
        ->toMatchArray(['created' => 1, 'updated' => 0, 'skipped' => 0])
        ->and($stale->fresh()->external_id)->toBeNull()
        ->and(Student::query()->where('nisn', '1234512345')->value('external_id'))->toBe('7');
});

test('admin sidebar contains four separate student navigation entries', function () {
    $this->actingAs(studentAdmin())->get(route('admin.dashboard'))->assertSuccessful()
        ->assertSee('Data Siswa MI')->assertSee('Data Siswa SMP')->assertSee('Kelas MI')->assertSee('Kelas SMP');
});

test('student and class row actions use accessible tonal icon buttons', function () {
    Student::factory()->create(['school_level' => 'mi', 'nama_lengkap' => 'Aisyah']);
    SchoolClass::factory()->create(['school_level' => 'mi', 'name' => '4 A']);
    $admin = studentAdmin();

    $this->actingAs($admin)->get(route('admin.students.index', 'mi'))
        ->assertSuccessful()
        ->assertSee('admin-row-action-edit', false)
        ->assertSee('admin-row-action-delete', false)
        ->assertSee('aria-label="Edit siswa Aisyah"', false)
        ->assertSee('aria-label="Hapus siswa Aisyah"', false)
        ->assertSee('M11 5H6a2 2 0 00-2 2v11', false)
        ->assertSee('M19 7l-.867 12.142', false)
        ->assertDontSee('fas fa-pen', false)
        ->assertDontSee('fas fa-trash', false);

    $this->actingAs($admin)->get(route('admin.school-classes.index', 'mi'))
        ->assertSuccessful()
        ->assertSee('aria-label="Edit kelas 4 A"', false)
        ->assertSee('aria-label="Hapus kelas 4 A"', false)
        ->assertSee('M11 5H6a2 2 0 00-2 2v11', false)
        ->assertSee('M19 7l-.867 12.142', false)
        ->assertDontSee('fas fa-pen', false)
        ->assertDontSee('fas fa-trash', false);
});
