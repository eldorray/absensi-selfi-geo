<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $academic_year_id
 * @property int $school_class_id
 * @property int $teacher_id
 * @property ?int $assigned_by
 * @property-read ?AcademicYear $academicYear
 * @property-read ?SchoolClass $schoolClass
 * @property-read ?User $teacher
 * @property-read ?User $assignedBy
 */
class HomeroomAssignment extends Model
{
    /** @use HasFactory<\Database\Factories\HomeroomAssignmentFactory> */
    use HasFactory;

    protected $fillable = ['academic_year_id', 'school_class_id', 'teacher_id', 'assigned_by'];

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
