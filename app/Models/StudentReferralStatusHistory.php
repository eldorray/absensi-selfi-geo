<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $student_referral_id
 * @property int $actor_id
 * @property ?string $from_status
 * @property string $to_status
 * @property ?string $safe_summary
 * @property ?\Illuminate\Support\Carbon $transitioned_at
 * @property-read ?StudentReferral $referral
 * @property-read ?User $actor
 */
class StudentReferralStatusHistory extends Model
{
    /** @use HasFactory<\Database\Factories\StudentReferralStatusHistoryFactory> */
    use HasFactory;

    protected $fillable = ['actor_id', 'from_status', 'to_status', 'safe_summary', 'transitioned_at'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['transitioned_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<StudentReferral, $this>
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(StudentReferral::class, 'student_referral_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
