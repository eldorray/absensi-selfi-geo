<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed offices first
        $this->call(OfficeSeeder::class);

        // Create default roles
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin',
            'is_admin' => true,
            'description' => 'Administrator dengan akses penuh ke sistem',
        ]);

        $kepalaSekolahRole = Role::create([
            'name' => 'Kepala Sekolah',
            'slug' => 'kepala-sekolah',
            'is_admin' => false,
            'description' => 'Kepala Sekolah',
        ]);

        $guruRole = Role::create([
            'name' => 'Guru',
            'slug' => 'guru',
            'is_admin' => false,
            'description' => 'Tenaga Pendidik',
        ]);

        $tendikRole = Role::create([
            'name' => 'Tendik',
            'slug' => 'tendik',
            'is_admin' => false,
            'description' => 'Tenaga Kependidikan',
        ]);

        // Create admin user
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
            'office_id' => null,
        ]);

        // Create sample users for each role
        User::factory()->create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@example.com',
            'role_id' => $kepalaSekolahRole->id,
            'office_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'Guru Test',
            'email' => 'guru@example.com',
            'role_id' => $guruRole->id,
            'office_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'Tendik Test',
            'email' => 'tendik@example.com',
            'role_id' => $tendikRole->id,
            'office_id' => 1,
        ]);
    }
}
