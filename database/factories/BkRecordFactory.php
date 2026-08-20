<?php

namespace Database\Factories;

use App\Models\BkRecord;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BkRecordFactory extends Factory
{
    protected $model = BkRecord::class;

    public function definition(): array
    {
        return [
            'counselor_id' => User::factory(),
            'student_id' => Student::factory(),
            'school_level' => 'mi',
            'record_type' => 'counseling',
            'occurred_at' => now(),
            'custom_topic' => fake()->words(3, true),
            'counseling_content' => fake()->paragraph(),
            'counseling_result' => fake()->sentence(),
            'status' => 'new',
            'status_updated_at' => now(),
        ];
    }
}
