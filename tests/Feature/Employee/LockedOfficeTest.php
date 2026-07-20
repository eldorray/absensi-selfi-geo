<?php

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

afterEach(fn () => Carbon::setTestNow());

function lockGuruRole(): Role
{
    return Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
}

function lockOffice(string $name, float $lat, float $lng): Office
{
    return Office::create([
        'name' => $name,
        'latitude' => $lat,
        'longitude' => $lng,
        'radius_meters' => 100,
    ]);
}

test('selfie page locks the office picker to the assigned office', function () {
    $smp = lockOffice('Kantor SMP', -6.20, 106.80);
    lockOffice('Kantor MI', -7.00, 107.00);

    $user = User::factory()->create(['role_id' => lockGuruRole()->id, 'office_id' => $smp->id]);

    $this->actingAs($user)
        ->get(route('attendance.selfie'))
        ->assertStatus(200)
        ->assertSee('Terkunci oleh admin')
        ->assertSee('Kantor SMP')
        ->assertDontSee('Kantor MI')
        ->assertDontSee('-- Pilih Lokasi Kerja --');
});

test('selfie page shows all offices when the user has no assigned office', function () {
    lockOffice('Kantor SMP', -6.20, 106.80);
    lockOffice('Kantor MI', -7.00, 107.00);

    $user = User::factory()->create(['role_id' => lockGuruRole()->id, 'office_id' => null]);

    $this->actingAs($user)
        ->get(route('attendance.selfie'))
        ->assertStatus(200)
        ->assertDontSee('Terkunci oleh admin')
        ->assertSee('Kantor SMP')
        ->assertSee('Kantor MI');
});

test('check-in geofencing uses the assigned office, ignoring a tampered office_id', function () {
    Storage::fake('public');

    $smp = lockOffice('Kantor SMP', -6.20, 106.80);
    $mi = lockOffice('Kantor MI', -7.00, 107.00); // far away

    $user = User::factory()->create(['role_id' => lockGuruRole()->id, 'office_id' => $smp->id]);

    $monday = Carbon::parse('2026-07-20 07:00:00');
    WorkSchedule::create([
        'user_id' => $user->id,
        'day' => 'senin',
        'check_in_time' => '07:00:00',
        'check_out_time' => '16:00:00',
        'is_active' => true,
    ]);
    Carbon::setTestNow($monday);

    // Standing at the SMP office, but submitting the MI office id.
    $response = $this->actingAs($user)->postJson(route('attendance.store'), [
        'office_id' => $mi->id,
        'latitude' => -6.20,
        'longitude' => 106.80,
        'image_base64' => 'x',
    ]);

    // The assigned (SMP) office is used, so geofencing passes — no location error.
    $errors = $response->json('errors') ?? [];
    expect($errors['location'] ?? null)->toBeNull();
});
