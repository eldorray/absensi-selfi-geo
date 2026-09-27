<?php

use App\Models\Leave;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;

// Two changes made for the teacher PWA deliberately reach the admin panel and the iOS app too.

test('the iOS profile API sends two-letter initials', function () {
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create(['name' => 'MI Daarul Hikmah', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id, 'name' => 'Siti Nur Rahmawati']);

    $this->actingAs($user, 'sanctum')->getJson('/api/profile')
        ->assertOk()
        ->assertJsonPath('initials', 'SN');
});

test('admin pagination uses the Indonesian labels', function () {
    $admin = User::factory()->create(['role_id' => Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true])->id]);
    $teacher = User::factory()->create(['role_id' => Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false])->id]);
    foreach (range(1, 20) as $i) {
        Leave::create(['user_id' => $teacher->id, 'type' => 'izin', 'start_date' => '2026-07-01', 'end_date' => '2026-07-01', 'reason' => "Alasan {$i}", 'status' => 'pending']);
    }

    $this->actingAs($admin)->get(route('admin.leaves.index'))
        ->assertOk()
        ->assertSee('Berikutnya', false)
        ->assertDontSee('Next &raquo;', false);
});
