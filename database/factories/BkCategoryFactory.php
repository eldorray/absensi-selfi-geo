<?php

namespace Database\Factories;

use App\Models\BkCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class BkCategoryFactory extends Factory
{
    protected $model = BkCategory::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'record_type' => 'violation',
            'default_severity' => 'light',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
