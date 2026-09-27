<?php

declare(strict_types=1);

use App\Models\BkRecord;
use App\Models\Office;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\User;
use Illuminate\Support\Js;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
});

function uiEmployee(string $name, ?Office $office = null): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['name' => $name, 'role_id' => $role->id, 'office_id' => $office?->id]);
}

test('work schedules are paginated on the server and keep the filters in the links', function () {
    $office = Office::create(['name' => 'SMP', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    for ($i = 1; $i <= 30; $i++) {
        uiEmployee(sprintf('Pegawai %02d', $i), $office);
    }

    actingAs($this->admin)->get(route('admin.work-schedules.index', ['office_id' => $office->id]))
        ->assertOk()
        ->assertViewHas('users', fn ($users) => $users->perPage() === 25 && $users->total() === 30)
        ->assertSee('Pegawai 25')
        ->assertDontSee('Pegawai 26')
        ->assertSee('office_id='.$office->id.'&amp;page=2', false);

    actingAs($this->admin)->get(route('admin.work-schedules.index', ['office_id' => $office->id, 'page' => 2]))
        ->assertOk()
        ->assertSee('Pegawai 30')
        ->assertDontSee('Pegawai 01');
});

test('work schedules search filters on the server', function () {
    uiEmployee('Guru Pencarian');
    uiEmployee('Guru Lainnya');

    actingAs($this->admin)->get(route('admin.work-schedules.index', ['search' => 'Pencarian']))
        ->assertOk()
        ->assertSee('Guru Pencarian')
        ->assertDontSee('Guru Lainnya')
        ->assertSee('value="Pencarian"', false);
});

test('delete confirmations name the item and escape it for javascript', function () {
    $user = uiEmployee("Budi O'Neil");

    actingAs($this->admin)->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee(Js::from("Hapus user Budi O'Neil? Tindakan ini tidak dapat dibatalkan.")->toHtml(), false)
        ->assertSee('aria-label="Hapus user Budi O&#039;Neil"', false)
        ->assertSee('admin-row-action-delete', false)
        ->assertDontSee('Yakin ingin menghapus user ini?')
        ->assertDontSee('No Role');

    Role::create(['name' => 'Tamu', 'slug' => 'tamu', 'is_admin' => false]);
    actingAs($this->admin)->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee(Js::from('Hapus role Tamu? Tindakan ini tidak dapat dibatalkan.')->toHtml(), false)
        ->assertSee('aria-label="Edit role Tamu"', false);
});

test('an admin form with a validation error marks the field invalid', function () {
    actingAs($this->admin)->from(route('admin.users.create'))
        ->followingRedirects()
        ->post(route('admin.users.store'), ['name' => '', 'email' => 'bukan-email'])
        ->assertOk()
        ->assertSee('aria-invalid="true" aria-describedby="name-error"', false)
        ->assertSee('<p id="name-error" class="admin-hint admin-text-danger">', false)
        ->assertSee('aria-describedby="email-error"', false)
        ->assertDontSee('aria-describedby="office_id-error"', false);
});

test('admin forms mark required fields visually', function () {
    actingAs($this->admin)->get(route('admin.offices.create'))
        ->assertOk()
        ->assertSee('Nama Kantor <span aria-hidden="true" class="admin-text-danger">*</span>', false)
        ->assertDontSee('aria-invalid', false);
});

test('bk pages show human labels and keep the level filter selection', function () {
    BkRecord::factory()->create(['status' => 'in_progress', 'record_type' => 'violation', 'school_level' => 'smp']);

    actingAs($this->admin)->get(route('admin.bk-records.index', ['school_level' => 'smp']))
        ->assertOk()
        ->assertSee('admin-table', false)
        ->assertSee('Dalam penanganan')
        ->assertSee('Pelanggaran')
        ->assertDontSee('>in_progress<', false)
        ->assertSee('<option value="smp" selected>SMP</option>', false);

    actingAs($this->admin)->get(route('admin.bk-categories.index'))
        ->assertOk()
        ->assertSee('Tingkat Keparahan')
        ->assertDontSee('Severity')
        ->assertSee('admin-empty-state', false);
});

test('referral oversight page shows a readable status', function () {
    $student = Student::factory()->create(['school_level' => 'mi']);
    $referral = StudentReferral::query()->create([
        'student_id' => $student->id,
        'created_by' => User::factory()->create()->id,
        'school_level' => 'mi',
        'reason' => 'Perlu pendampingan',
        'observation' => 'Perubahan perilaku teramati.',
        'observed_at' => today(),
        'urgency' => 'normal',
        'status' => 'in_handling',
    ]);

    actingAs($this->admin)->get(route('admin.kesiswaan.referrals.show', $referral))
        ->assertOk()
        ->assertSee('Dalam penanganan')
        ->assertDontSee('in_handling')
        ->assertSee('Belum ditangani');
});
