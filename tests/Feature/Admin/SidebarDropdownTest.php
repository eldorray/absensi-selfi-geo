<?php

use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('admin sidebar renders the grouped dropdowns', function () {
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);
    $admin = User::factory()->create(['role_id' => $role->id]);

    actingAs($admin)->get(route('admin.dashboard'))
        ->assertStatus(200)
        ->assertSee('Master Data')
        ->assertSee('Kehadiran')
        ->assertSee('Informasi')
        ->assertSee('data-layout-sidebar', false)
        ->assertSee('app-sidebar-collapsed', false)
        ->assertSee('app-sidebar-expanded', false)
        ->assertSee('open && sidebarOpen', false)
        ->assertSee('temporarilyOpenSidebar()', false)
        ->assertSee('closeSidebarOnMobile()', false)
        ->assertSee('window.innerWidth < 768', false)
        ->assertSee('@click="closeSidebarOnMobile()"', false)
        ->assertSee('admin-nav-icon', false)
        ->assertSee('aria-expanded', false);
});
