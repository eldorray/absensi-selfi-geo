<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $student_referral_id
 * @property int $uploaded_by
 * @property string $path
 * @property string $original_name
 * @property ?string $mime_type
 * @property ?int $size_bytes
 * @property-read ?StudentReferral $referral
 * @property-read ?User $uploader
 */
class StudentReferralAttachment extends Model
{
    /** @use HasFactory<\Database\Factories\StudentReferralAttachmentFactory> */
    use HasFactory;

    protected $fillable = ['uploaded_by', 'path', 'original_name', 'mime_type', 'size_bytes'];

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
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
