<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $counselor_id
 * @property int $student_id
 * @property ?int $student_referral_id
 * @property ?int $category_id
 * @property string $school_level
 * @property string $record_type
 * @property ?\Illuminate\Support\Carbon $occurred_at
 * @property ?string $custom_topic
 * @property ?string $severity
 * @property ?string $chronology
 * @property ?string $action_taken
 * @property ?string $counseling_content
 * @property ?string $counseling_result
 * @property ?string $follow_up_plan
 * @property ?\Illuminate\Support\Carbon $next_follow_up_at
 * @property string $status
 * @property ?\Illuminate\Support\Carbon $status_updated_at
 * @property ?\Illuminate\Support\Carbon $archived_at
 * @property ?int $archived_by
 * @property-read ?User $counselor
 * @property-read ?Student $student
 * @property-read ?StudentReferral $referral
 * @property-read ?BkCategory $category
 * @property-read ?User $archivedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Student> $relatedStudents
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BkAttachment> $attachments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BkFollowUp> $followUps
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BkParentContact> $parentContacts
 */
class BkRecord extends Model
{
    /** @use HasFactory<\Database\Factories\BkRecordFactory> */
    use HasFactory;

    public const TYPES = ['violation', 'counseling'];

    public const STATUSES = ['new', 'in_progress', 'waiting_follow_up', 'completed'];

    public const SEVERITIES = ['light', 'medium', 'heavy'];

    protected $fillable = ['counselor_id', 'student_id', 'student_referral_id', 'category_id', 'school_level', 'record_type', 'occurred_at', 'custom_topic', 'severity', 'chronology', 'action_taken', 'counseling_content', 'counseling_result', 'follow_up_plan', 'next_follow_up_at', 'status', 'status_updated_at', 'archived_at', 'archived_by'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['occurred_at' => 'datetime', 'next_follow_up_at' => 'datetime', 'status_updated_at' => 'datetime', 'archived_at' => 'datetime'];
    }

    /**
     * @param  Builder<BkRecord>  $query
     * @return Builder<BkRecord>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isAdmin() ? $query : $query->where('counselor_id', $user->id)->where('school_level', $user->office?->school_level);
    }

    /**
     * @param  Builder<BkRecord>  $query
     * @return Builder<BkRecord>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * @param  Builder<BkRecord>  $query
     * @return Builder<BkRecord>
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<StudentReferral, $this>
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(StudentReferral::class, 'student_referral_id');
    }

    /**
     * @return BelongsTo<BkCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BkCategory::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * @return BelongsToMany<Student, $this>
     */
    public function relatedStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'bk_record_related_students');
    }

    /**
     * @return HasMany<BkAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(BkAttachment::class);
    }

    /**
     * @return HasMany<BkFollowUp, $this>
     */
    public function followUps(): HasMany
    {
        return $this->hasMany(BkFollowUp::class)->oldest('followed_up_at');
    }

    /**
     * @return HasMany<BkParentContact, $this>
     */
    public function parentContacts(): HasMany
    {
        return $this->hasMany(BkParentContact::class)->oldest('contacted_at');
    }
}
