<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

/**
 * OfficeSeeder - Creates sample office locations for testing.
 */
class OfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offices = [
            [
                'name' => 'Kantor Pusat Jakarta',
                'latitude' => -6.20000000,
                'longitude' => 106.81666667,
                'radius_meters' => 100,
            ],
            [
                'name' => 'Kantor Cabang Bandung',
                'latitude' => -6.91474000,
                'longitude' => 107.60981000,
                'radius_meters' => 150,
            ],
            [
                'name' => 'Kantor Cabang Surabaya',
                'latitude' => -7.25750000,
                'longitude' => 112.75083300,
                'radius_meters' => 100,
            ],
        ];

        foreach ($offices as $office) {
            Office::create($office);
        }
    }
}
