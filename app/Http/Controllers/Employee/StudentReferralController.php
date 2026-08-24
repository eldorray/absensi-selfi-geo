<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentReferralRequest;
use App\Http\Requests\TransitionStudentReferralRequest;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\StudentReferralAttachment;
use App\Services\Kesiswaan\ReferralService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class StudentReferralController extends Controller
{
    public function create(Request $r, Student $student)
    {
        $this->authorizeHomeroomStudent($r, $student);
        Gate::authorize('create', StudentReferral::class);

        return view('attendance.kesiswaan.referral-form', compact('student'));
    }

    public function store(StoreStudentReferralRequest $r, Student $student, ReferralService $s): RedirectResponse
    {
        $this->authorizeHomeroomStudent($r, $student);
        $ref = $s->create($student, $r->user(), $r->safe()->except('attachments'), $r->file('attachments', []));

        return redirect()->route('attendance.kesiswaan.referrals.show', $ref);
    }

    public function show(StudentReferral $referral)
    {
        Gate::authorize('view', $referral);
        $referral->load(['student', 'creator', 'counselor', 'attachments', 'histories.actor', 'bkRecord']);

        return view('attendance.kesiswaan.referral', compact('referral'));
    }

    public function claim(Request $r, StudentReferral $referral, ReferralService $s): RedirectResponse
    {
        Gate::authorize('claim', $referral);
        $s->claim($referral, $r->user());

        return back()->with('success', 'Rujukan berhasil diambil.');
    }

    public function transition(TransitionStudentReferralRequest $r, StudentReferral $referral, ReferralService $s): RedirectResponse
    {
        $s->transition($referral, $r->user(), $r->validated('status'), $r->validated('safe_summary'));

        return back()->with('success', 'Status rujukan diperbarui.');
    }

    public function attachment(StudentReferral $referral, StudentReferralAttachment $attachment)
    {
        Gate::authorize('view', $referral);
        abort_unless($attachment->student_referral_id === $referral->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name, ['Content-Type' => $attachment->mime_type]);
    }

    private function authorizeHomeroomStudent(Request $request, Student $student): void
    {
        $assignment = $request->user()->activeHomeroomAssignment();

        abort_unless(
            $assignment
                && $student->status === 'Aktif'
                && $student->school_class_id === $assignment->school_class_id,
            404
        );
    }
}
