<?php

use App\Models\Role;
use App\Models\User;

it('renders the Material 3 welcome gateway for guests', function () {
    $response = $this->get(route('home'));

    $response->assertSuccessful()
        ->assertSee('Presensi yang siap saat Anda tiba.')
        ->assertSee('Verifikasi swafoto')
        ->assertSee('Verifikasi lokasi')
        ->assertSee('Masuk ke AbsenKu')
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('aria-label="Aktifkan tema gelap"', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('@media (min-width: 800px)', false)
        ->assertSee('@media (max-width: 359px)', false)
        ->assertSee('grid-template-columns: repeat(2, minmax(0, 1fr))', false)
        ->assertSee('class="phone-shell"', false)
        ->assertSee('class="dynamic-island"', false)
        ->assertSee('width: 395px', false)
        ->assertDontSee('animate-blob');
});

it('links authenticated employees to their dashboard', function () {
    $role = Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );
    $user = User::factory()->create(['role_id' => $role->id]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertSuccessful()
        ->assertSee('Selamat datang kembali,')
        ->assertSee($user->name)
        ->assertSee('Buka Dashboard')
        ->assertSee('href="'.route('attendance.dashboard').'"', false);
});

it('links authenticated administrators to the admin dashboard', function () {
    $role = Role::firstOrCreate(
        ['slug' => 'admin'],
        ['name' => 'Admin', 'is_admin' => true],
    );
    $user = User::factory()->create(['role_id' => $role->id]);

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertSuccessful()
        ->assertSee('Buka Dashboard')
        ->assertSee('href="'.route('admin.dashboard').'"', false);
});
