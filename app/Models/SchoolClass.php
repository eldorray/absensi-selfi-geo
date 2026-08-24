<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $school_level
 * @property string $name
 * @property string $normalized_name
 * @property ?int $grade_level
 * @property bool $is_active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Student> $students
 * @property-read \Illuminate\Database\Eloquent\Collection<int, HomeroomAssignment> $homeroomAssignments
 */
class SchoolClass extends Model
{
    /** @use HasFactory<\Database\Factories\SchoolClassFactory> */
    use HasFactory;

    protected $fillable = ['school_level', 'name', 'normalized_name', 'grade_level', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['grade_level' => 'integer', 'is_active' => 'boolean'];
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * @return HasMany<HomeroomAssignment, $this>
     */
    public function homeroomAssignments(): HasMany
    {
        return $this->hasMany(HomeroomAssignment::class);
    }

    public static function normalizeName(string $name): string
    {
        return Str::of($name)->squish()->lower()->value();
    }
}
