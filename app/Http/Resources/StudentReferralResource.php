<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\StudentReferral;
use App\Models\StudentReferralAttachment;
use App\Models\StudentReferralStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Rujukan siswa dari wali kelas ke Guru BK.
 *
 * `observation` adalah isi rujukan yang ditulis pembuatnya, bukan catatan
 * profesional BK, jadi boleh dibaca setiap pihak yang lolos policy `view`.
 * Isi Catatan BK yang tertaut TIDAK pernah ikut — hanya penanda ada/tidaknya.
 *
 * @property-read StudentReferral $resource
 */
class StudentReferralResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $referral = $this->resource;

        return [
            'id' => $referral->id,
            'student' => $this->whenLoaded(
                'student',
                fn () => new StudentResource($referral->student)
            ),
            'student_id' => $referral->student_id,
            'school_level' => $referral->school_level,
            'reason' => $referral->reason,
            'observation' => $referral->observation,
            'observed_at' => $referral->observed_at?->format('Y-m-d'),
            'urgency' => $referral->urgency->value,
            'urgency_label' => match ($referral->urgency->value) {
                'urgent' => 'Mendesak',
                'important' => 'Penting',
                default => 'Normal',
            },
            'status' => $referral->status->value,
            'status_label' => match ($referral->status->value) {
                'new' => 'Baru',
                'in_handling' => 'Ditangani',
                'completed' => 'Selesai',
                default => 'Ditolak',
            },
            'safe_summary' => $referral->safe_summary,
            'created_by' => $referral->created_by,
            'creator_name' => $this->whenLoaded('creator', fn () => $referral->creator?->name),
            'assigned_counselor_id' => $referral->assigned_counselor_id,
            'counselor_name' => $this->whenLoaded('counselor', fn () => $referral->counselor?->name),
            'claimed_at' => $referral->claimed_at?->toIso8601String(),
            'completed_at' => $referral->completed_at?->toIso8601String(),
            'rejected_at' => $referral->rejected_at?->toIso8601String(),
            'created_at' => $referral->created_at?->toIso8601String(),
            'attachments' => $this->whenLoaded('attachments', fn (): array => $referral->attachments->map(fn (StudentReferralAttachment $attachment): array => [
                'id' => $attachment->id,
                'name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size_bytes' => $attachment->size_bytes,
                'download_url' => url("/api/kesiswaan/referrals/{$referral->id}/attachments/{$attachment->id}"),
            ])->all()),
            'histories' => $this->whenLoaded('histories', fn (): array => $referral->histories->map(fn (StudentReferralStatusHistory $history): array => [
                'id' => $history->id,
                'from_status' => $history->from_status,
                'to_status' => $history->to_status,
                'safe_summary' => $history->safe_summary,
                'actor_name' => $history->actor?->name,
                'transitioned_at' => $history->transitioned_at?->toIso8601String(),
            ])->all()),
            // Hanya penanda; isi Catatan BK tidak pernah dikirim lewat rujukan.
            'has_bk_record' => $this->whenLoaded('bkRecord', fn (): bool => $referral->bkRecord !== null),
            'attachments_count' => $this->whenCounted('attachments'),
        ];
    }
}
