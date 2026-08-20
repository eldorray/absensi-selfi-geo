<?php

use App\Models\BkRecord;
use App\Models\Office;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function apiBkUser(string $level = 'mi', bool $admin = false): User
{
    $role = Role::firstOrCreate(['slug' => $admin ? 'admin-bk-api' : 'guru-bk-api'], ['name' => $admin ? 'Admin BK API' : 'Guru BK API', 'is_admin' => $admin]);
    $office = Office::create(['name' => 'API '.uniqid(), 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100, 'school_level' => $level]);

    return User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id, 'is_bk_counselor' => ! $admin]);
}
function apiBkRecord(User $user, Student $student): BkRecord
{
    return BkRecord::create(['counselor_id' => $user->id, 'student_id' => $student->id, 'school_level' => $student->school_level, 'record_type' => 'counseling', 'occurred_at' => now(), 'status' => 'new']);
}

test('bk android endpoints enforce authentication and capability', function () {
    $this->getJson('/api/bk/records')->assertUnauthorized();
    $user = apiBkUser();
    $user->update(['is_bk_counselor' => false]);
    $this->actingAs($user, 'sanctum')->getJson('/api/bk/records')->assertForbidden();
});

test('students and records are unit and owner scoped', function () {
    $owner = apiBkUser('mi');
    $other = apiBkUser('mi');
    $mi = Student::factory()->create(['school_level' => 'mi', 'status' => 'Aktif']);
    $smp = Student::factory()->create(['school_level' => 'smp', 'status' => 'Aktif']);
    $record = apiBkRecord($other, $mi);
    $this->actingAs($owner, 'sanctum')->getJson('/api/bk/students')->assertOk()->assertJsonFragment(['id' => $mi->id])->assertJsonMissing(['id' => $smp->id]);
    $this->actingAs($owner, 'sanctum')->getJson("/api/bk/records/{$record->id}")->assertNotFound();
});

test('counselor can create update archive restore and add child resources', function () {
    Storage::fake('local');
    $user = apiBkUser();
    $student = Student::factory()->create(['school_level' => 'mi', 'status' => 'Aktif']);
    $create = $this->actingAs($user, 'sanctum')->postJson('/api/bk/records', ['student_id' => $student->id, 'record_type' => 'counseling', 'occurred_at' => now()->toISOString(), 'custom_topic' => 'Anxiety']);
    $create->assertCreated()->assertJsonPath('data.custom_topic', 'Anxiety');
    $id = $create->json('data.id');
    $this->patchJson("/api/bk/records/$id", ['status' => 'in_progress'])->assertOk()->assertJsonPath('data.status', 'in_progress');
    $follow = $this->postJson("/api/bk/records/$id/follow-ups", ['followed_up_at' => now()->toISOString(), 'progress_notes' => 'Improving'])->assertCreated();
    $this->patchJson("/api/bk/records/$id/follow-ups/{$follow->json('id')}", ['result' => 'Good'])->assertOk()->assertJsonPath('result', 'Good');
    $contact = $this->postJson("/api/bk/records/$id/parent-contacts", ['contacted_at' => now()->toISOString(), 'method' => 'phone', 'contact_name' => 'Parent', 'summary' => 'Discussed'])->assertCreated();
    $this->patchJson("/api/bk/records/$id/parent-contacts/{$contact->json('id')}", ['summary' => 'Updated'])->assertOk()->assertJsonPath('summary', 'Updated');
    $this->getJson("/api/bk/records/$id")->assertOk()->assertJsonPath('data.parent_contacted', true);
    $this->postJson("/api/bk/records/$id/archive")->assertOk();
    $this->postJson("/api/bk/records/$id/restore")->assertOk();
});

test('attachments validate limits and download privately while admins are read only', function () {
    Storage::fake('local');
    $user = apiBkUser();
    $student = Student::factory()->create(['school_level' => 'mi', 'status' => 'Aktif']);
    $response = $this->actingAs($user, 'sanctum')->post('/api/bk/records', ['student_id' => $student->id, 'record_type' => 'violation', 'occurred_at' => now()->toISOString(), 'attachments' => [UploadedFile::fake()->image('proof.jpg')]], ['Accept' => 'application/json']);
    $response->assertCreated();
    $id = $response->json('data.id');
    $attachment = $response->json('data.attachments.0.id');
    $this->get("/api/bk/records/$id/attachments/$attachment", ['Accept' => 'application/json'])->assertOk();
    $admin = apiBkUser('mi', true);
    $this->actingAs($admin, 'sanctum')->getJson("/api/bk/records/$id")->assertOk();
    $this->patchJson("/api/bk/records/$id", ['status' => 'completed'])->assertForbidden();
});
