<?php

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;

function makeUser(bool $admin): User
{
    $role = Role::create([
        'name' => $admin ? 'Admin' : 'Guru',
        'slug' => $admin ? 'admin' : 'guru',
        'is_admin' => $admin,
    ]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('admin can create an announcement', function () {
    $this->actingAs(makeUser(admin: true));

    $this->post(route('admin.announcements.store'), [
        'title' => 'Pengumuman',
        'summary' => 'Ringkas',
        'body' => 'Isi lengkap informasi.',
        'is_active' => '1',
        'sort_order' => '0',
    ])->assertRedirect(route('admin.announcements.index'));

    expect(Announcement::where('title', 'Pengumuman')->exists())->toBeTrue();
});

test('non-admin cannot access announcement management', function () {
    $this->actingAs(makeUser(admin: false));

    $this->get(route('admin.announcements.index'))->assertRedirect(route('attendance.dashboard'));
    $this->post(route('admin.announcements.store'), [
        'title' => 'X',
        'body' => 'Y',
    ])->assertRedirect(route('attendance.dashboard'));

    expect(Announcement::count())->toBe(0);
});

test('store requires title and body', function () {
    $this->actingAs(makeUser(admin: true));

    $this->post(route('admin.announcements.store'), [])
        ->assertSessionHasErrors(['title', 'body']);
});

test('employee dashboard shows active announcements', function () {
    Announcement::create(['title' => 'Kartu Aktif', 'body' => 'isi', 'is_active' => true]);
    Announcement::create(['title' => 'Kartu Nonaktif', 'body' => 'isi', 'is_active' => false]);

    $this->actingAs(makeUser(admin: false));

    $this->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('Kartu Aktif')
        ->assertDontSee('Kartu Nonaktif');
});

test('detail page 404s for an inactive announcement', function () {
    $active = Announcement::create(['title' => 'Aktif', 'body' => 'isi', 'is_active' => true]);
    $inactive = Announcement::create(['title' => 'Nonaktif', 'body' => 'isi', 'is_active' => false]);

    $this->actingAs(makeUser(admin: false));

    $this->get(route('attendance.information.show', $active))->assertStatus(200)->assertSee('Aktif');
    $this->get(route('attendance.information.show', $inactive))->assertNotFound();
});
