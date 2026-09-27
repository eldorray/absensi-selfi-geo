<?php

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);
    $this->admin = User::factory()->create(['role_id' => $role->id]);
});

test('confirm modal scopes its ids and describes the message', function () {
    actingAs($this->admin)->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('x-id="[\'confirm-title\', \'confirm-message\']"', false)
        ->assertSee(':aria-describedby="$id(\'confirm-message\')"', false)
        ->assertSee('trapFocus($event)', false);
});

test('sidebar items keep an accessible name when collapsed', function () {
    actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('x-show="!sidebarOpen" class="sr-only"', false);
});

test('student filters have screen reader labels', function () {
    actingAs($this->admin)->get(route('admin.students.index', 'mi'))
        ->assertOk()
        ->assertSee('<label for="student-search" class="sr-only">Cari siswa</label>', false)
        ->assertSee('<label for="student-class-filter" class="sr-only">Filter kelas</label>', false);
});

test('work schedule rows expose a keyboard toggle', function () {
    $employeeRole = Role::firstOrCreate(['slug' => 'karyawan'], ['name' => 'Karyawan', 'is_admin' => false]);
    $employee = User::factory()->create(['role_id' => $employeeRole->id]);

    actingAs($this->admin)->get(route('admin.work-schedules.index'))
        ->assertOk()
        ->assertSee('aria-controls="schedule-detail-'.$employee->id.'"', false)
        ->assertSee('for="work-schedule-office-filter"', false)
        ->assertDontSee('x-collapse', false);
});

test('daily report photo preview is an accessible dialog', function () {
    actingAs($this->admin)->get(route('admin.reports.daily'))
        ->assertOk()
        ->assertSee('role="dialog"', false)
        ->assertSee(':aria-labelledby="$id(\'photo-modal-title\')"', false)
        ->assertSee('aria-label="Tutup"', false);
});
