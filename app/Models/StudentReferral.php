<?php

namespace App\Models;

use App\Enums\StudentReferralStatus;
use App\Enums\StudentReferralUrgency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $student_id
 * @property int $created_by
 * @property string $school_level
 * @property ?int $assigned_counselor_id
 * @property string $reason
 * @property string $observation
 * @property ?\Illuminate\Support\Carbon $observed_at
 * @property StudentReferralUrgency $urgency
 * @property StudentReferralStatus $status
 * @property ?string $safe_summary
 * @property ?\Illuminate\Support\Carbon $claimed_at
 * @property ?\Illuminate\Support\Carbon $completed_at
 * @property ?\Illuminate\Support\Carbon $rejected_at
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 * @property-read ?Student $student
 * @property-read ?User $creator
 * @property-read ?User $counselor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StudentReferralAttachment> $attachments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StudentReferralStatusHistory> $histories
 * @property-read ?BkRecord $bkRecord
 * @property-read ?int $attachments_count
 */
class StudentReferral extends Model
{
    /** @use HasFactory<\Database\Factories\StudentReferralFactory> */
    use HasFactory;

    protected $fillable = ['student_id', 'created_by', 'school_level', 'assigned_counselor_id', 'reason', 'observation', 'observed_at', 'urgency', 'status', 'safe_summary', 'claimed_at', 'completed_at', 'rejected_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['observed_at' => 'date', 'claimed_at' => 'datetime', 'completed_at' => 'datetime', 'rejected_at' => 'datetime', 'urgency' => StudentReferralUrgency::class, 'status' => StudentReferralStatus::class];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_counselor_id');
    }

    /**
     * @return HasMany<StudentReferralAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(StudentReferralAttachment::class);
    }

    /**
     * @return HasMany<StudentReferralStatusHistory, $this>
     */
    public function histories(): HasMany
    {
        return $this->hasMany(StudentReferralStatusHistory::class)->oldest('transitioned_at');
    }

    /**
     * @return HasOne<BkRecord, $this>
     */
    public function bkRecord(): HasOne
    {
        return $this->hasOne(BkRecord::class);
    }

    /**
     * @param  Builder<StudentReferral>  $q
     * @return Builder<StudentReferral>
     */
    public function scopeVisibleTo(Builder $q, User $u): Builder
    {
        if ($u->isAdmin()) {
            return $q;
        } if ($u->is_bk_counselor && in_array($u->office?->school_level, Student::LEVELS, true)) {
            return $q->where('school_level', $u->office->school_level)->where(fn ($x) => $x->where('status', 'new')->orWhere('assigned_counselor_id', $u->id));
        }

        return $q->where('created_by', $u->id);
    }
}
