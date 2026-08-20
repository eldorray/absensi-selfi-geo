<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HomeroomAssignment> */
class HomeroomAssignmentFactory extends Factory
{
    protected $model = HomeroomAssignment::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'school_class_id' => SchoolClass::factory(),
            'teacher_id' => User::factory(),
            'assigned_by' => null,
        ];
    }
}
