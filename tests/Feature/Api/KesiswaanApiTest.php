<?php

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\Office;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function kesiswaanApiUser(array $flags = [], string $level = 'mi'): User
{
    $role = Role::firstOrCreate(['slug' => 'guru-kesiswaan-api'], ['name' => 'Guru Kesiswaan API', 'is_admin' => false]);
    $office = Office::create(['name' => 'Unit '.uniqid(), 'school_level' => $level, 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);

    return User::factory()->create($flags + ['role_id' => $role->id, 'office_id' => $office->id]);
}

function kesiswaanApiHomeroom(User $teacher, string $level = 'mi'): SchoolClass
{
    $year = AcademicYear::firstOrCreate(['name' => '2026/2027'], ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $name = 'Kelas '.uniqid();
    $class = SchoolClass::create(['school_level' => $level, 'name' => $name, 'normalized_name' => SchoolClass::normalizeName($name), 'grade_level' => 1, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    return $class;
}

function kesiswaanApiStudent(?SchoolClass $class = null, string $level = 'mi'): Student
{
    return Student::create([
        'school_class_id' => $class?->id,
        'school_level' => $class?->school_level ?? $level,
        'source' => 'manual',
        'nama_lengkap' => 'Siswa '.uniqid(),
        'nisn' => fake()->unique()->numerify('##########'),
        'status' => 'Aktif',
    ]);
}

test('direktori kesiswaan hanya untuk petugas yang ditunjuk dan terikat jenjang', function () {
    $mi = kesiswaanApiStudent(null, 'mi');
    $smp = kesiswaanApiStudent(null, 'smp');

    $this->getJson('/api/kesiswaan/students')->assertUnauthorized();
    $this->actingAs(kesiswaanApiUser(), 'sanctum')->getJson('/api/kesiswaan/students')->assertForbidden();

    $officer = kesiswaanApiUser(['is_student_affairs_officer' => true], 'mi');
    $this->actingAs($officer, 'sanctum')->getJson('/api/kesiswaan/students')
        ->assertOk()
        ->assertJsonPath('scope.school_level', 'mi')
        ->assertJsonFragment(['id' => $mi->id])
        ->assertJsonMissing(['id' => $smp->id]);

    $this->getJson("/api/kesiswaan/students/{$smp->id}")->assertNotFound();
});

test('profil kesiswaan memuat ringkasan bk aman tanpa isi profesional', function () {
    $officer = kesiswaanApiUser(['is_student_affairs_officer' => true]);
    $teacher = kesiswaanApiUser();
    $class = kesiswaanApiHomeroom($teacher);
    $student = kesiswaanApiStudent($class);

    \App\Models\BkRecord::create(['counselor_id' => kesiswaanApiUser()->id, 'student_id' => $student->id, 'school_level' => 'mi', 'record_type' => 'counseling', 'occurred_at' => now(), 'counseling_content' => 'Isi profesional rahasia', 'status' => 'new']);

    $response = $this->actingAs($officer, 'sanctum')->getJson("/api/kesiswaan/students/{$student->id}")->assertOk();

    $response->assertJsonPath('student.id', $student->id)
        ->assertJsonPath('homeroom_teacher', $teacher->name)
        ->assertJsonStructure(['bk_summary' => ['active_count', 'types', 'statuses', 'needs_follow_up']]);

    expect($response->getContent())->not->toContain('Isi profesional rahasia');
});

test('wali kelas membuat rujukan dengan lampiran dan menolak siswa kelas lain', function () {
    Storage::fake('local');
    $teacher = kesiswaanApiUser();
    $class = kesiswaanApiHomeroom($teacher);
    $student = kesiswaanApiStudent($class);
    $outsider = kesiswaanApiStudent(kesiswaanApiHomeroom(kesiswaanApiUser()));

    $payload = [
        'reason' => 'Perlu pendampingan',
        'observation' => 'Perubahan perilaku teramati sejak pekan lalu.',
        'observed_at' => today()->toDateString(),
        'urgency' => 'important',
        'attachments' => [UploadedFile::fake()->image('bukti.jpg')],
    ];

    $created = $this->actingAs($teacher, 'sanctum')
        ->post("/api/my-class/students/{$student->id}/referrals", $payload, ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.status', 'new')
        ->assertJsonPath('data.urgency_label', 'Penting')
        ->assertJsonPath('data.status_label', 'Baru');

    expect($created->json('data.attachments'))->toHaveCount(1);

    $this->post("/api/my-class/students/{$outsider->id}/referrals", $payload, ['Accept' => 'application/json'])
        ->assertNotFound();

    $this->getJson('/api/kesiswaan/referrals/mine')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.id', $created->json('data.id'));
});

test('antrean guru bk memprioritaskan mendesak dan claim bersifat eksklusif', function () {
    $teacher = kesiswaanApiUser();
    $class = kesiswaanApiHomeroom($teacher);

    $counselor = kesiswaanApiUser(['is_bk_counselor' => true]);
    $rival = kesiswaanApiUser(['is_bk_counselor' => true]);

    $normal = StudentReferral::create(['student_id' => kesiswaanApiStudent($class)->id, 'created_by' => $teacher->id, 'school_level' => 'mi', 'reason' => 'Biasa', 'observation' => 'Catatan.', 'observed_at' => today(), 'urgency' => 'normal', 'status' => 'new']);
    $urgent = StudentReferral::create(['student_id' => kesiswaanApiStudent($class)->id, 'created_by' => $teacher->id, 'school_level' => 'mi', 'reason' => 'Mendesak', 'observation' => 'Catatan.', 'observed_at' => today(), 'urgency' => 'urgent', 'status' => 'new']);

    $this->actingAs(kesiswaanApiUser(), 'sanctum')->getJson('/api/kesiswaan/referrals/queue')->assertForbidden();

    $this->actingAs($counselor, 'sanctum')->getJson('/api/kesiswaan/referrals/queue')
        ->assertOk()
        ->assertJsonPath('data.0.id', $urgent->id)
        ->assertJsonPath('data.1.id', $normal->id);

    $this->postJson("/api/kesiswaan/referrals/{$urgent->id}/claim")
        ->assertOk()
        ->assertJsonPath('data.status', 'in_handling')
        ->assertJsonPath('data.assigned_counselor_id', $counselor->id);

    $this->actingAs($rival, 'sanctum')->postJson("/api/kesiswaan/referrals/{$urgent->id}/claim")->assertForbidden();
    $this->actingAs($rival, 'sanctum')->getJson("/api/kesiswaan/referrals/{$urgent->id}")->assertForbidden();
});

test('transisi rujukan wajib ringkasan aman dan hanya oleh penanggung jawab', function () {
    $teacher = kesiswaanApiUser();
    $class = kesiswaanApiHomeroom($teacher);
    $counselor = kesiswaanApiUser(['is_bk_counselor' => true]);

    $referral = StudentReferral::create(['student_id' => kesiswaanApiStudent($class)->id, 'created_by' => $teacher->id, 'school_level' => 'mi', 'reason' => 'Perlu', 'observation' => 'Catatan.', 'observed_at' => today(), 'urgency' => 'normal', 'status' => 'in_handling', 'assigned_counselor_id' => $counselor->id, 'claimed_at' => now()]);

    $this->actingAs($counselor, 'sanctum')
        ->patchJson("/api/kesiswaan/referrals/{$referral->id}/transition", ['status' => 'completed'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('safe_summary');

    $this->patchJson("/api/kesiswaan/referrals/{$referral->id}/transition", ['status' => 'completed', 'safe_summary' => 'Lanjutkan pemantauan umum.'])
        ->assertOk()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.status_label', 'Selesai')
        ->assertJsonPath('data.safe_summary', 'Lanjutkan pemantauan umum.');

    $this->actingAs($teacher, 'sanctum')->getJson('/api/kesiswaan/referrals/mine')
        ->assertOk()
        ->assertJsonPath('data.0.status', 'completed');
});

test('metadata rujukan menyebutkan kapabilitas dan batas lampiran', function () {
    $teacher = kesiswaanApiUser();
    $class = kesiswaanApiHomeroom($teacher);

    $this->actingAs($teacher, 'sanctum')->getJson('/api/kesiswaan/referrals/meta')
        ->assertOk()
        ->assertJsonPath('is_homeroom_teacher', true)
        ->assertJsonPath('homeroom_class', $class->name)
        ->assertJsonPath('is_bk_counselor', false)
        ->assertJsonPath('limits.max_attachments', 3)
        ->assertJsonPath('urgency_labels.urgent', 'Mendesak');
});

test('notifikasi kesiswaan terkirim ke guru bk dan dapat ditandai terbaca', function () {
    $teacher = kesiswaanApiUser();
    $class = kesiswaanApiHomeroom($teacher);
    $student = kesiswaanApiStudent($class);
    $counselor = kesiswaanApiUser(['is_bk_counselor' => true]);

    $this->actingAs($teacher, 'sanctum')->postJson("/api/my-class/students/{$student->id}/referrals", [
        'reason' => 'Perlu pendampingan',
        'observation' => 'Catatan pengamatan.',
        'observed_at' => today()->toDateString(),
        'urgency' => 'normal',
    ])->assertCreated();

    $list = $this->actingAs($counselor, 'sanctum')->getJson('/api/kesiswaan/notifications')
        ->assertOk()
        ->assertJsonPath('unread_count', 1)
        ->assertJsonPath('data.0.status', 'new')
        ->assertJsonPath('data.0.title', 'Rujukan baru')
        ->assertJsonPath('data.0.student_name', $student->nama_lengkap);

    expect($list->json('data.0.referral_id'))->not->toBeNull();

    $this->postJson("/api/kesiswaan/notifications/{$list->json('data.0.id')}/read")
        ->assertOk()
        ->assertJsonPath('data.read_at', fn ($value) => $value !== null);

    $this->postJson('/api/kesiswaan/notifications/read-all')->assertOk()->assertJsonPath('unread_count', 0);
    $this->getJson('/api/kesiswaan/notifications?unread=1')->assertOk()->assertJsonPath('meta.total', 0);
});
