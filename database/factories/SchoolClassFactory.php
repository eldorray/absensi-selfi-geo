<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $level = fake()->randomElement(['mi', 'smp']);
        $name = ($level === 'mi' ? fake()->numberBetween(1, 6) : fake()->numberBetween(7, 9)).fake()->randomElement(['A', 'B']);

        return ['school_level' => $level, 'name' => $name, 'normalized_name' => SchoolClass::normalizeName($name), 'is_active' => true];
    }
}
