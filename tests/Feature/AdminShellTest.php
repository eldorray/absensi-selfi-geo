<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;

function adminShellUser(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('success and error flashes render once in the layout with live-region roles', function () {
    $response = actingAs(adminShellUser())
        ->withSession(['success' => 'Data tersimpan-unik.', 'error' => 'Gagal menyimpan-unik.'])
        ->get(route('settings.appearance.edit'))
        ->assertOk();

    $html = $response->getContent();

    expect(substr_count($html, 'Data tersimpan-unik.'))->toBe(1)
        ->and(substr_count($html, 'Gagal menyimpan-unik.'))->toBe(1);

    $response
        ->assertSee('role="status" data-flash="success"', false)
        ->assertSee('role="alert" data-flash="error"', false)
        ->assertSee('admin-alert-success', false)
        ->assertSee('admin-alert-danger', false);
});

test('settings controllers flash readable Indonesian status copy', function () {
    $user = adminShellUser();
    $user->update(['password' => Hash::make('lama12345')]);

    actingAs($user)
        ->from(route('settings.password.edit'))
        ->put(route('settings.password.update'), [
            'current_password' => 'lama12345',
            'password' => 'baru123456',
            'password_confirmation' => 'baru123456',
        ])
        ->assertSessionHas('status', 'Password berhasil diperbarui.');
});

test('document title includes the page title when given', function () {
    actingAs(adminShellUser())
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('<title>Kelola Role · '.config('app.name').'</title>', false);
});

test('document title falls back to the app name', function () {
    actingAs(adminShellUser())
        ->get(route('settings.profile.edit'))
        ->assertOk()
        ->assertSee('<title>'.config('app.name').'</title>', false);
});

test('header menus use Indonesian labels and expose their state', function () {
    actingAs(adminShellUser())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pengaturan')
        ->assertSee('Keluar')
        ->assertDontSee('>Logout<', false)
        ->assertSee('aria-haspopup="true"', false)
        ->assertSee('aria-controls="profile-menu"', false)
        ->assertSee('aria-controls="app-sidebar"', false)
        ->assertSee(':aria-expanded="sidebarOpen"', false);
});

test('settings pages have no leftover English labels', function () {
    $user = adminShellUser();

    actingAs($user)->get(route('settings.password.edit'))
        ->assertOk()
        ->assertSee('Password saat ini')
        ->assertDontSee('Current Password')
        ->assertDontSee('Confirm Password');

    actingAs($user)->get(route('settings.profile.edit'))
        ->assertOk()
        ->assertSee('Hapus akun')
        ->assertDontSee('Delete account');
});

test('shell ships the global submit guard and green theme color', function () {
    actingAs(adminShellUser())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('window.markFormBusy', false)
        ->assertSee('<meta name="theme-color" content="#176b43">', false)
        ->assertDontSee('formSubmitted', false)
        ->assertDontSee('#4f46e5', false);
});
