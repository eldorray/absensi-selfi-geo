<?php

// tests/Feature/Employee/ProfilPagesTest.php

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;

function profilUser(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id, 'name' => 'Siti Rahmawati']);
}

test('profile offers the theme picker, password link and logout', function () {
    $html = $this->actingAs(profilUser())->get(route('attendance.profile'))
        ->assertSuccessful()
        ->assertSee('Profil Saya')
        ->assertSee('Siti Rahmawati')
        ->assertSee('Tema tampilan')
        ->assertSee('guruSetTheme(mode)', false)
        ->assertSee('href="'.route('attendance.password').'"', false)
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee('Keluar')
        ->getContent();

    expect(substr_count($html, 'name="appearance"'))->toBe(3);
});

test('password form labels every field and offers show/hide toggles', function () {
    $html = $this->actingAs(profilUser())->get(route('attendance.password'))
        ->assertSee('Ganti Password')
        ->assertSee('for="current_password"', false)
        ->assertSee('autocomplete="current-password"', false)
        ->assertSee('autocomplete="new-password"', false)
        ->getContent();

    expect(substr_count($html, ':aria-pressed="show.toString()"'))->toBe(3);
});

test('announcement detail renders title and body', function () {
    $user = profilUser();
    $announcement = Announcement::create(['title' => 'Rapat Guru', 'summary' => 'Jumat pagi', 'body' => 'Rapat di aula.', 'is_active' => true]);

    $this->actingAs($user)->get(route('attendance.information.show', $announcement))
        ->assertSuccessful()
        ->assertSee('Rapat Guru')
        ->assertSee('Rapat di aula.');
});
