<?php

use App\Models\BkRecord;
use App\Models\Office;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Klien Android mengunggah lampiran lewat POST + _method=PUT karena PHP tidak
 * mengurai multipart pada PUT. Test ini mengunci perilaku tersebut.
 */
function bkUploadCounselor(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create([
        'name' => 'Upload '.uniqid(),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
        'school_level' => 'mi',
    ]);

    return User::factory()->create([
        'role_id' => $role->id,
        'office_id' => $office->id,
        'is_bk_counselor' => true,
    ]);
}

it('menerima unggahan lampiran lewat POST dengan _method spoofing', function () {
    Storage::fake('local');
    $counselor = bkUploadCounselor();
    $student = Student::factory()->create(['school_level' => 'mi']);
    $record = BkRecord::factory()->create([
        'counselor_id' => $counselor->id,
        'student_id' => $student->id,
        'school_level' => 'mi',
    ]);

    $response = $this->actingAs($counselor, 'sanctum')->post("/api/bk/records/{$record->id}", [
        '_method' => 'PUT',
        'attachments' => [UploadedFile::fake()->image('bukti.jpg')],
    ]);

    $response->assertOk();
    expect($record->attachments()->count())->toBe(1);
    expect($response->json('data.attachments.0.name'))->toBe('bukti.jpg');
});

it('menolak lampiran melebihi batas lima berkas', function () {
    Storage::fake('local');
    $counselor = bkUploadCounselor();
    $student = Student::factory()->create(['school_level' => 'mi']);
    $record = BkRecord::factory()->create([
        'counselor_id' => $counselor->id,
        'student_id' => $student->id,
        'school_level' => 'mi',
    ]);

    $files = collect(range(1, 6))
        ->map(fn (int $i) => UploadedFile::fake()->image("bukti{$i}.jpg"))
        ->all();

    // Tanpa `Accept: application/json` Laravel membalas redirect 302, bukan 422.
    // Interceptor OkHttp di Android selalu mengirim header ini, jadi test harus sama.
    $this->actingAs($counselor, 'sanctum')
        ->post(
            "/api/bk/records/{$record->id}",
            ['_method' => 'PUT', 'attachments' => $files],
            ['Accept' => 'application/json'],
        )
        ->assertStatus(422);
});
