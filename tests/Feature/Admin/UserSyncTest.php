<?php

use App\Models\Role;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

test('user has mass-assignable nip and nik columns', function () {
    $role = Role::create(['name' => 'Guru', 'slug' => 'guru', 'is_admin' => false]);

    $user = User::create([
        'name' => 'Budi',
        'email' => 'budi@guru.local',
        'password' => 'secret-hash',
        'role_id' => $role->id,
        'nip' => '199001011000',
        'nik' => '3200010101900001',
    ]);

    expect($user->fresh())
        ->nip->toBe('199001011000')
        ->nik->toBe('3200010101900001');
});

function guruRole(): Role
{
    return Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );
}

function adminUser(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'administrator'],
        ['name' => 'Administrator', 'is_admin' => true],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

test('sync creates new users from the selected source with guru role and nip password', function () {
    guruRole();

    Http::fake([
        '*/api/guru-mi/all*' => Http::response([
            'data' => [
                ['full_name' => 'Budi MI', 'nik' => '3201010101900001'],
            ],
            'current_page' => 1,
            'last_page' => 1,
        ], 200),
        '*/api/guru-smp/all*' => Http::response([
            'data' => [
                ['full_name' => 'Siti SMP', 'nik' => '3201010101900002'],
            ],
            'current_page' => 1,
            'last_page' => 1,
        ], 200),
    ]);

    $result = app(UserSyncService::class)->sync('guru-mi');

    expect($result)
        ->created->toBe(1)
        ->updated->toBe(0)
        ->failed->toBe(0);

    // Only the requested source is fetched.
    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/guru-mi/all'));
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/api/guru-smp/all'));

    $budi = User::where('nip', '3201010101900001')->first();
    expect($budi)->not->toBeNull()
        ->and($budi->name)->toBe('Budi MI')
        ->and($budi->email)->toBe('3201010101900001@guru.local')
        ->and($budi->role->slug)->toBe('guru')
        ->and($budi->office_id)->toBeNull()
        ->and(Hash::check('3201010101900001', $budi->password))->toBeTrue();

    // The other unit is untouched.
    expect(User::where('nip', '3201010101900002')->exists())->toBeFalse();
});

test('sync only fetches the guru-smp source when selected', function () {
    guruRole();

    Http::fake([
        '*/api/guru-smp/all*' => Http::response([
            'data' => [['full_name' => 'Siti SMP', 'nik' => '3201010101900002']],
            'current_page' => 1, 'last_page' => 1,
        ], 200),
    ]);

    $result = app(UserSyncService::class)->sync('guru-smp');

    expect($result['created'])->toBe(1);
    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/guru-smp/all'));
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/api/guru-mi/all'));
    expect(User::where('nip', '3201010101900002')->exists())->toBeTrue();
});

test('sync throws on an invalid source', function () {
    guruRole();

    app(UserSyncService::class)->sync('siswa-mi');
})->throws(RuntimeException::class);

test('re-sync updates existing user by nip without duplicating', function () {
    guruRole();

    // Http::fake() merges new stub callbacks onto the existing collection and
    // resolution returns the first match, so registering the fake twice with
    // Http::response() would keep returning the first payload. A sequence is
    // required so the two sync() calls made below observe different bodies.
    Http::fake([
        '*/api/guru-mi/all*' => Http::sequence()
            ->push([
                'data' => [['full_name' => 'Budi Awal', 'nik' => '3201010101900001']],
                'current_page' => 1, 'last_page' => 1,
            ])
            ->push([
                'data' => [['full_name' => 'Budi Revisi', 'nik' => '3201010101900001']],
                'current_page' => 1, 'last_page' => 1,
            ]),
    ]);

    app(UserSyncService::class)->sync('guru-mi');

    $result = app(UserSyncService::class)->sync('guru-mi');

    expect(User::where('nip', '3201010101900001')->count())->toBe(1)
        ->and($result['created'])->toBe(0)
        ->and($result['updated'])->toBe(1)
        ->and(User::where('nip', '3201010101900001')->first()->name)->toBe('Budi Revisi');
});

test('re-sync does not overwrite manually assigned role and office', function () {
    $guru = guruRole();
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'administrator', 'is_admin' => true]);
    $office = \App\Models\Office::create([
        'name' => 'Kantor Pusat',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);

    $existing = User::create([
        'name' => 'Budi Lama',
        'email' => 'budi.custom@sekolah.id',
        'password' => Hash::make('rahasia'),
        'role_id' => $adminRole->id,
        'office_id' => $office->id,
        'nip' => '3201010101900001',
        'nik' => '3201010101900001',
    ]);

    Http::fake([
        '*/api/guru-mi/all*' => Http::response([
            'data' => [['full_name' => 'Budi Baru', 'nik' => '3201010101900001']],
            'current_page' => 1, 'last_page' => 1,
        ], 200),
    ]);

    app(UserSyncService::class)->sync('guru-mi');

    $existing->refresh();
    expect($existing->name)->toBe('Budi Baru')
        ->and($existing->role_id)->toBe($adminRole->id)
        ->and($existing->office_id)->toBe($office->id)
        ->and($existing->email)->toBe('budi.custom@sekolah.id')
        ->and(Hash::check('rahasia', $existing->password))->toBeTrue();
});

test('admin can trigger user sync for a chosen source and sees a success flash', function () {
    guruRole();

    Http::fake([
        '*/api/guru-mi/all*' => Http::response([
            'data' => [['full_name' => 'Budi MI', 'nik' => '3201010101900001']],
            'current_page' => 1, 'last_page' => 1,
        ], 200),
    ]);

    $this->actingAs(adminUser())
        ->post(route('admin.users.sync'), ['source' => 'guru-mi'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(User::where('nip', '3201010101900001')->exists())->toBeTrue();
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/api/guru-smp/all'));
});

test('sync request is rejected without a valid source', function () {
    guruRole();
    Http::fake();

    $this->actingAs(adminUser())
        ->post(route('admin.users.sync'), ['source' => 'siswa-mi'])
        ->assertSessionHasErrors('source');

    $this->actingAs(adminUser())
        ->post(route('admin.users.sync'))
        ->assertSessionHasErrors('source');

    Http::assertNothingSent();
    expect(User::whereNotNull('nip')->count())->toBe(0);
});

test('non-admin cannot trigger user sync', function () {
    guruRole();

    $employee = User::factory()->create([
        'role_id' => Role::create(['name' => 'Guru2', 'slug' => 'guru2', 'is_admin' => false])->id,
    ]);

    $this->actingAs($employee)
        ->post(route('admin.users.sync'), ['source' => 'guru-mi'])
        ->assertRedirect(route('attendance.dashboard'));

    Http::assertNothingSent();
});

test('connection failure shows an error flash and creates no users', function () {
    guruRole();

    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('down');
    });

    $this->actingAs(adminUser())
        ->post(route('admin.users.sync'), ['source' => 'guru-mi'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(User::whereNotNull('nip')->count())->toBe(0);
});
