<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'school_level' => fake()->randomElement(Student::LEVELS),
            'source' => 'manual',
            'nama_lengkap' => fake()->name(),
            'nisn' => fake()->unique()->numerify('##########'),
            'nik' => fake()->unique()->numerify('################'),
            'status' => 'Aktif',
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
        ];
    }
}
