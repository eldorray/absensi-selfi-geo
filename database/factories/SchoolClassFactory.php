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

    /**
     * normalized_name menopang unique (school_level, normalized_name), jadi ia harus
     * ikut nama akhir. Tanpa ini, override ['name' => ...] menyisakan normalized_name
     * acak bawaan factory dan memicu bentrok unique yang bergantung urutan test.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (SchoolClass $class): void {
            $class->normalized_name = SchoolClass::normalizeName($class->name);
        });
    }
}
