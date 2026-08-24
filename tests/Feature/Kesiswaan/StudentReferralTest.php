<?php

use App\Models\BkRecord;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\User;
use App\Services\Kesiswaan\ReferralService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function referral(array $overrides = []): StudentReferral
{
    $creator = User::factory()->create();
    $student = Student::factory()->create(['school_level' => 'mi']);

    return StudentReferral::query()->create(array_merge([
        'student_id' => $student->id,
        'created_by' => $creator->id,
        'school_level' => 'mi',
        'reason' => 'Perlu pendampingan',
        'observation' => 'Perubahan perilaku teramati.',
        'observed_at' => today(),
        'urgency' => 'normal',
        'status' => 'new',
    ], $overrides));
}

test('claim atomik menetapkan satu penanggung jawab dan riwayat dalam transaksi', function () {
    $record = referral();
    $first = User::factory()->create();
    $second = User::factory()->create();
    $service = app(ReferralService::class);

    $service->claim($record, $first);

    expect($record->fresh()->status->value)->toBe('in_handling')
        ->and($record->fresh()->assigned_counselor_id)->toBe($first->id)
        ->and($record->histories()->where('from_status', 'new')->where('to_status', 'in_handling')->where('actor_id', $first->id)->exists())->toBeTrue();

    expect(fn () => $service->claim($record->fresh(), $second))->toThrow(ValidationException::class, 'Rujukan telah diambil Guru BK lain.');
    expect($record->fresh()->assigned_counselor_id)->toBe($first->id);
});

test('transisi ilegal ditolak dan penyelesaian menyimpan ringkasan serta riwayat', function () {
    $counselor = User::factory()->create();
    $record = referral(['status' => 'in_handling', 'assigned_counselor_id' => $counselor->id, 'claimed_at' => now()]);
    $service = app(ReferralService::class);

    expect(fn () => $service->transition($record, User::factory()->create(), 'completed', 'Aman'))->toThrow(ValidationException::class);
    $service->transition($record->fresh(), $counselor, 'completed', 'Lanjutkan pemantauan umum.');

    expect($record->fresh()->status->value)->toBe('completed')
        ->and($record->fresh()->safe_summary)->toBe('Lanjutkan pemantauan umum.')
        ->and($record->fresh()->completed_at)->not->toBeNull()
        ->and($record->histories()->where('to_status', 'completed')->where('safe_summary', 'Lanjutkan pemantauan umum.')->exists())->toBeTrue();
});

test('satu rujukan hanya dapat terhubung ke satu catatan BK dan tidak ada route hard delete', function () {
    $counselor = User::factory()->create();
    $record = referral(['status' => 'in_handling', 'assigned_counselor_id' => $counselor->id]);
    $payload = ['counselor_id' => $counselor->id, 'student_id' => $record->student_id, 'student_referral_id' => $record->id, 'school_level' => 'mi', 'record_type' => 'violation', 'occurred_at' => now(), 'custom_topic' => 'Topik', 'severity' => 'light', 'chronology' => 'Kronologi privat', 'action_taken' => 'Tindakan', 'status' => 'new', 'status_updated_at' => now()];
    BkRecord::query()->create($payload);

    expect(fn () => BkRecord::query()->create($payload))->toThrow(QueryException::class);
    expect(collect(Route::getRoutes())->contains(fn ($route) => in_array('DELETE', $route->methods(), true) && str_contains($route->uri(), 'kesiswaan')))->toBeFalse();
});

test('validasi rujukan menggunakan bahasa Indonesia', function () {
    $request = new \App\Http\Requests\StoreStudentReferralRequest;

    expect($request->messages()['urgency.in'])->toContain('Urgensi')
        ->and($request->messages()['observed_at.before_or_equal'])->toContain('tidak boleh melebihi hari ini')
        ->and($request->messages()['attachments.*.mimetypes'])->toContain('JPG, PNG, atau PDF')
        ->and($request->messages()['attachments.*.max'])->toContain('5 MB');
});

test('profil Kesiswaan menggunakan arah visual profil sebagai pusat pada PWA dan admin', function () {
    $employeeView = file_get_contents(resource_path('views/attendance/kesiswaan/show.blade.php'));
    $adminView = file_get_contents(resource_path('views/admin/kesiswaan/show.blade.php'));

    expect($employeeView)
        ->toContain('<x-layouts.mobile')
        ->toContain('data-kesiswaan-design="profile-centered"')
        ->toContain('data-profile-hero="student"')
        ->toContain('data-profile-summary="student"')
        ->toContain('Informasi pribadi')
        ->toContain('Ringkasan BK')
        ->toContain('Buat rujukan ke Guru BK')
        ->and($adminView)
        ->toContain('data-kesiswaan-design="profile-centered-admin"')
        ->toContain('lg:grid-cols-[minmax(0,1.45fr)_minmax(19rem,0.75fr)]')
        ->toContain('Informasi siswa')
        ->toContain('Ringkasan BK yang aman')
        ->toContain('Halaman ini hanya-baca');
});

test('daftar Kesiswaan memakai permukaan Material 3 yang responsif', function () {
    $employeeView = file_get_contents(resource_path('views/attendance/kesiswaan/index.blade.php'));
    $adminView = file_get_contents(resource_path('views/admin/kesiswaan/index.blade.php'));

    expect($employeeView)
        ->toContain('<x-layouts.mobile')
        ->toContain('data-kesiswaan-list="mobile"')
        ->toContain('data-kesiswaan-hero="directory"')
        ->toContain('data-kesiswaan-search="students"')
        ->toContain('Cakupan siswa')
        ->toContain('Direktori siswa')
        ->toContain('id="student-directory"')
        ->toContain('data-kesiswaan-pagination="stable"')
        ->toContain("links('pagination::simple-tailwind')")
        ->and($adminView)
        ->toContain('data-kesiswaan-list="admin"')
        ->toContain('admin-glass-panel')
        ->toContain('admin-field');
});
