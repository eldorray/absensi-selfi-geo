<?php

use App\Models\Leave;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

test('employee can navigate from dashboard to leaves index and see leave list', function () {
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $guru = User::factory()->create(['role_id' => $role->id]);

    Leave::create([
        'user_id' => $guru->id,
        'type' => 'izin',
        'reason' => 'Keperluan keluarga penting',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
        'status' => 'pending',
    ]);

    actingAs($guru)->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee(route('attendance.leaves.index'));

    actingAs($guru)->get(route('attendance.leaves.index'))
        ->assertStatus(200)
        ->assertSee('Perizinan Saya')
        ->assertSee('Keperluan keluarga penting')
        ->assertSee(route('attendance.leaves.create'));
});

test('web leave submission enforces twelve hours notice for izin but exempts sakit', function () {
    \Carbon\Carbon::setTestNow('2026-08-17 12:01:00');
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $guru = User::factory()->create(['role_id' => $role->id]);

    actingAs($guru)->post(route('attendance.leaves.store'), [
        'type' => 'izin',
        'start_date' => '2026-08-18',
        'end_date' => '2026-08-18',
        'reason' => 'Keperluan keluarga.',
    ])->assertSessionHasErrors('start_date');

    actingAs($guru)->post(route('attendance.leaves.store'), [
        'type' => 'sakit',
        'start_date' => '2026-08-17',
        'end_date' => '2026-08-17',
        'reason' => 'Sedang sakit.',
    ])->assertRedirect(route('attendance.leaves.index'));

    expect(Leave::query()->count())->toBe(1);
    \Carbon\Carbon::setTestNow();
});

test('employee can open create leave page and submit leave request successfully', function () {
    Storage::fake('public');

    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $guru = User::factory()->create(['role_id' => $role->id]);

    actingAs($guru)->get(route('attendance.leaves.create'))
        ->assertStatus(200)
        ->assertSee('Ajukan Izin')
        ->assertSee('Jenis Perizinan')
        ->assertSee('Kirim Pengajuan Izin');

    $response = actingAs($guru)->post(route('attendance.leaves.store'), [
        'type' => 'sakit',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(1)->toDateString(),
        'reason' => 'Demam tinggi dan flu, istirahat dokter',
        'attachment' => UploadedFile::fake()->image('surat_dokter.jpg', 600, 800),
    ]);

    $response->assertRedirect(route('attendance.leaves.index'))
        ->assertSessionHas('success');

    $leave = Leave::where('user_id', $guru->id)->where('reason', 'Demam tinggi dan flu, istirahat dokter')->first();
    expect($leave)->not->toBeNull();
    expect($leave->type)->toBe('sakit');
    expect($leave->status)->toBe('pending');
    expect($leave->attachment)->not->toBeNull();

    // Verify it renders on the leaves index page
    actingAs($guru)->get(route('attendance.leaves.index'))
        ->assertStatus(200)
        ->assertSee('Demam tinggi dan flu, istirahat dokter')
        ->assertSee('Sakit');
});
