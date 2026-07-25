<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

function vpAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

function vpGuru(array $attributes = []): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(array_merge(['role_id' => $role->id], $attributes));
}

test('creating a user stores a readable, encrypted copy of the password', function () {
    $guruRoleId = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false])->id;

    actingAs(vpAdmin())->post(route('admin.users.store'), [
        'name' => 'Guru Baru',
        'email' => 'guru.baru@example.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
        'role_id' => $guruRoleId,
    ])->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'guru.baru@example.com')->firstOrFail();

    // The cast decrypts back to the plaintext...
    expect($user->visible_password)->toBe('rahasia123');
    // ...but the raw column is ciphertext, not the plaintext.
    $raw = DB::table('users')->where('id', $user->id)->value('visible_password');
    expect($raw)->not->toBe('rahasia123');
    // ...and login still works against the hash.
    expect(Hash::check('rahasia123', $user->password))->toBeTrue();
});

test('resetting a password updates the visible copy', function () {
    $guru = vpGuru();
    $guru->update(['visible_password' => 'lama123']);

    actingAs(vpAdmin())->put(route('admin.users.update', $guru), [
        'name' => $guru->name,
        'email' => $guru->email,
        'role_id' => $guru->role_id,
        'password' => 'baru45678',
        'password_confirmation' => 'baru45678',
    ])->assertRedirect(route('admin.users.index'));

    $guru->refresh();
    expect($guru->visible_password)->toBe('baru45678');
    expect(Hash::check('baru45678', $guru->password))->toBeTrue();
});

test('updating a user without a password leaves the visible copy untouched', function () {
    $guru = vpGuru();
    $guru->update(['visible_password' => 'tetap123']);

    actingAs(vpAdmin())->put(route('admin.users.update', $guru), [
        'name' => 'Nama Diubah',
        'email' => $guru->email,
        'role_id' => $guru->role_id,
    ])->assertRedirect(route('admin.users.index'));

    $guru->refresh();
    expect($guru->name)->toBe('Nama Diubah');
    expect($guru->visible_password)->toBe('tetap123');
});

test('visible_password never leaks through serialization', function () {
    $guru = vpGuru();
    $guru->update(['visible_password' => 'secret999']);

    expect($guru->fresh()->toArray())->not->toHaveKey('visible_password');
});

test('a teacher changing their own password keeps the visible copy in sync', function () {
    $guru = vpGuru();
    $guru->update(['password' => Hash::make('old12345'), 'visible_password' => 'old12345']);

    actingAs($guru)->put(route('attendance.password.update'), [
        'current_password' => 'old12345',
        'password' => 'fresh6789',
        'password_confirmation' => 'fresh6789',
    ]);

    expect($guru->fresh()->visible_password)->toBe('fresh6789');
});

test('admin sees a teacher password on the users page', function () {
    vpGuru(['name' => 'Guru Terlihat'])->update(['visible_password' => 'lihataku1']);

    actingAs(vpAdmin())->get(route('admin.users.index'))
        ->assertStatus(200)
        ->assertSee('lihataku1');
});

test('password PDF export downloads for admin', function () {
    vpGuru()->update(['visible_password' => 'cetak1234']);

    actingAs(vpAdmin())->get(route('admin.users.export-pdf'))
        ->assertStatus(200)
        ->assertDownload('kredensial-guru.pdf');
});

test('non-admin cannot export the password PDF', function () {
    actingAs(vpGuru())->get(route('admin.users.export-pdf'))
        ->assertRedirect();
});

test('admin resets a password to the default and the visible copy follows', function () {
    $guru = vpGuru();
    $guru->update(['password' => Hash::make('sesuatu123'), 'visible_password' => 'sesuatu123']);

    actingAs(vpAdmin())->post(route('admin.users.reset-password', $guru))
        ->assertRedirect();

    $guru->refresh();
    expect(Hash::check('Guru12345', $guru->password))->toBeTrue();
    expect($guru->visible_password)->toBe('Guru12345');
});

test('non-admin cannot reset a password', function () {
    $target = vpGuru();

    actingAs(vpGuru())->post(route('admin.users.reset-password', $target))
        ->assertRedirect();

    expect(Hash::check('Guru12345', $target->fresh()->password))->toBeFalse();
});

test('editing a user returns to the same list page instead of page 1', function () {
    $guru = vpGuru();

    actingAs(vpAdmin())->put(route('admin.users.update', ['user' => $guru, 'page' => 3]), [
        'name' => 'Nama Baru',
        'email' => $guru->email,
        'role_id' => $guru->role_id,
    ])->assertRedirect(route('admin.users.index', ['page' => 3]));
});

test('the edit link carries the current list query', function () {
    $guru = vpGuru(['name' => 'Guru Halaman']);

    // Same `['user' => $user] + request()->query()` threading carries page too.
    actingAs(vpAdmin())->get(route('admin.users.index', ['search' => 'Halaman']))
        ->assertStatus(200)
        ->assertSee(route('admin.users.edit', ['user' => $guru, 'search' => 'Halaman']), false);
});
