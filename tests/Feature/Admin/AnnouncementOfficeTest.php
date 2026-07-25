<?php

use App\Models\Announcement;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

function annOffice(string $name): Office
{
    return Office::create(['name' => $name, 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
}

function annAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

function annGuru(?int $officeId): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id, 'office_id' => $officeId]);
}

test('a teacher sees global announcements and their own office, not other offices', function () {
    $smp = annOffice('SMP');
    $mi = annOffice('MI');

    $global = Announcement::create(['title' => 'Global', 'body' => 'x', 'is_active' => true, 'office_id' => null]);
    $smpAnn = Announcement::create(['title' => 'Khusus SMP', 'body' => 'x', 'is_active' => true, 'office_id' => $smp->id]);
    $miAnn = Announcement::create(['title' => 'Khusus MI', 'body' => 'x', 'is_active' => true, 'office_id' => $mi->id]);

    actingAs(annGuru($smp->id))->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('Global')
        ->assertSee('Khusus SMP')
        ->assertDontSee('Khusus MI');
});

test('a teacher without an office sees only global announcements', function () {
    $smp = annOffice('SMP');
    Announcement::create(['title' => 'Global', 'body' => 'x', 'is_active' => true, 'office_id' => null]);
    Announcement::create(['title' => 'Khusus SMP', 'body' => 'x', 'is_active' => true, 'office_id' => $smp->id]);

    actingAs(annGuru(null))->get(route('attendance.dashboard'))
        ->assertStatus(200)
        ->assertSee('Global')
        ->assertDontSee('Khusus SMP');
});

test('a teacher cannot open another office announcement by URL', function () {
    $smp = annOffice('SMP');
    $mi = annOffice('MI');
    $miAnn = Announcement::create(['title' => 'Khusus MI', 'body' => 'x', 'is_active' => true, 'office_id' => $mi->id]);

    actingAs(annGuru($smp->id))->get(route('attendance.information.show', $miAnn))
        ->assertNotFound();
});

test('a teacher can open a global and own-office announcement', function () {
    $smp = annOffice('SMP');
    $global = Announcement::create(['title' => 'Global', 'body' => 'x', 'is_active' => true, 'office_id' => null]);
    $own = Announcement::create(['title' => 'Khusus SMP', 'body' => 'x', 'is_active' => true, 'office_id' => $smp->id]);

    $guru = annGuru($smp->id);
    actingAs($guru)->get(route('attendance.information.show', $global))->assertStatus(200);
    actingAs($guru)->get(route('attendance.information.show', $own))->assertStatus(200);
});

test('admin can target an announcement at a specific office', function () {
    $smp = annOffice('SMP');

    actingAs(annAdmin())->post(route('admin.announcements.store'), [
        'title' => 'Rapat SMP',
        'body' => 'Isi',
        'office_id' => $smp->id,
        'sort_order' => 0,
        'is_active' => '1',
    ])->assertRedirect(route('admin.announcements.index'));

    expect(Announcement::where('title', 'Rapat SMP')->value('office_id'))->toBe($smp->id);
});

test('an empty office selection stores a global announcement', function () {
    actingAs(annAdmin())->post(route('admin.announcements.store'), [
        'title' => 'Untuk Semua',
        'body' => 'Isi',
        'office_id' => '',
        'sort_order' => 0,
        'is_active' => '1',
    ])->assertRedirect(route('admin.announcements.index'));

    expect(Announcement::where('title', 'Untuk Semua')->value('office_id'))->toBeNull();
});
