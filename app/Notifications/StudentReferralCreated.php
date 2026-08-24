<?php

namespace App\Notifications;

use App\Models\StudentReferral;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StudentReferralCreated extends Notification
{
    use Queueable;

    public function __construct(public StudentReferral $referral) {}

    public function via(object $n): array
    {
        return ['database'];
    }

    public function toArray(object $n): array
    {
        return ['referral_id' => $this->referral->id, 'student_name' => $this->referral->student->nama_lengkap, 'status' => 'new', 'url' => route('attendance.kesiswaan.referrals.show', $this->referral)];
    }
}
