<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentReferral;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReferralQueueController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless(
            $user->is_bk_counselor && in_array($user->office?->school_level, Student::LEVELS, true),
            403,
            'Anda tidak memiliki akses antrean Guru BK.'
        );

        $referrals = StudentReferral::visibleTo($user)
            ->with(['student.schoolClass', 'creator', 'counselor'])
            ->orderByRaw("CASE urgency WHEN 'urgent' THEN 1 WHEN 'important' THEN 2 ELSE 3 END")
            ->oldest()
            ->paginate(15);

        return view('attendance.kesiswaan.referral-queue', compact('referrals'));
    }
}
