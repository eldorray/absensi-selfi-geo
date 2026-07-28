<?php

declare(strict_types=1);

use App\Models\Leave;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function leaveTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

function leavePayload(array $overrides = []): array
{
    return array_merge([
        'type' => 'cuti',
        'start_date' => now()->addDay()->format('Y-m-d'),
        'end_date' => now()->addDays(2)->format('Y-m-d'),
        'reason' => 'Keperluan keluarga.',
    ], $overrides);
}

test('leaves index returns only the signed-in teacher rows', function () {
    $user = leaveTeacher();
    $other = leaveTeacher();

    Leave::create(['user_id' => $user->id, 'type' => 'cuti', 'start_date' => now()->addDay(), 'end_date' => now()->addDays(2), 'reason' => 'Milikku', 'status' => 'pending']);
    Leave::create(['user_id' => $other->id, 'type' => 'sakit', 'start_date' => now()->addDay(), 'end_date' => now()->addDays(2), 'reason' => 'Orang lain', 'status' => 'pending']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/leaves')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.reason', 'Milikku')
        ->assertJsonPath('data.0.type', 'cuti')
        ->assertJsonPath('data.0.type_label', 'Cuti')
        ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'total']]);

    expect($response->json('meta.total'))->toBe(1);
});

test('leaves index requires authentication', function () {
    $this->getJson('/api/leaves')->assertStatus(401);
});

test('leave store creates a pending request', function () {
    $user = leaveTeacher();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/leaves', leavePayload())
        ->assertStatus(201)
        ->assertJsonPath('type', 'cuti')
        ->assertJsonPath('status', 'pending')
        ->assertJsonPath('status_label', 'Menunggu')
        ->assertJsonPath('reason', 'Keperluan keluarga.');

    $leave = Leave::sole();
    expect($leave->user_id)->toBe($user->id)
        ->and($leave->status)->toBe('pending')
        ->and($leave->attachment)->toBeNull();
});

test('leave store accepts an image attachment', function () {
    Storage::fake('public');
    $user = leaveTeacher();

    $this->actingAs($user, 'sanctum')
        ->post('/api/leaves', leavePayload([
            'attachment' => UploadedFile::fake()->image('surat.jpg'),
        ]))
        ->assertStatus(201);

    $leave = Leave::sole();
    expect($leave->attachment)->not->toBeNull();
    Storage::disk('public')->assertExists($leave->attachment);
});

test('leave store validates the payload', function () {
    $user = leaveTeacher();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/leaves', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['type', 'start_date', 'end_date', 'reason']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/leaves', leavePayload(['type' => 'libur']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['type']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/leaves', leavePayload(['start_date' => now()->subDay()->format('Y-m-d')]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['start_date']);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/leaves', leavePayload([
            'start_date' => now()->addDays(3)->format('Y-m-d'),
            'end_date' => now()->addDay()->format('Y-m-d'),
        ]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['end_date']);
});

test('leave show returns the own row', function () {
    $user = leaveTeacher();
    $leave = Leave::create(['user_id' => $user->id, 'type' => 'izin', 'start_date' => now()->addDay(), 'end_date' => now()->addDays(2), 'reason' => 'Urusan', 'status' => 'pending']);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/leaves/{$leave->id}")
        ->assertStatus(200)
        ->assertJsonPath('id', $leave->id)
        ->assertJsonPath('type', 'izin')
        ->assertJsonPath('duration', 2);
});

test('leave show never exposes another teacher row', function () {
    $user = leaveTeacher();
    $other = leaveTeacher();
    $leave = Leave::create(['user_id' => $other->id, 'type' => 'izin', 'start_date' => now()->addDay(), 'end_date' => now()->addDays(2), 'reason' => 'Rahasia', 'status' => 'pending']);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/leaves/{$leave->id}")
        ->assertStatus(404);
});
