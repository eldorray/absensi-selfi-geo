<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\StudentReferralAttachment;
use App\Services\Kesiswaan\BkSummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KesiswaanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $students = Student::query()->with('schoolClass')
            ->when($search, fn ($q) => $q->where(fn ($x) => $x->where('nama_lengkap', 'like', "%{$search}%")->orWhere('nisn', 'like', "%{$search}%")->orWhere('nik', 'like', "%{$search}%")->orWhereHas('schoolClass', fn ($c) => $c->where('name', 'like', "%{$search}%"))))
            ->when($request->school_level, fn ($q, $v) => $q->where('school_level', $v))
            ->when($request->school_class_id, fn ($q, $v) => $q->where('school_class_id', $v))
            ->orderBy('nama_lengkap')->paginate(20)->withQueryString();
        $classes = SchoolClass::query()->where('is_active', true)->orderBy('school_level')->orderBy('name')->get();

        return view('admin.kesiswaan.index', compact('students', 'classes'));
    }

    public function show(Request $request, Student $student, BkSummaryService $summaryService)
    {
        $student->load(['schoolClass.homeroomAssignments' => fn ($q) => $q->with(['teacher', 'academicYear'])->whereHas('academicYear', fn ($x) => $x->where('is_active', true))]);
        $summary = $summaryService->for($student, $request->user());
        $referrals = $student->referrals()->with(['creator', 'counselor'])->latest()->paginate(10);

        return view('admin.kesiswaan.show', compact('student', 'summary', 'referrals'));
    }

    public function referral(StudentReferral $referral)
    {
        $referral->load(['student', 'creator', 'counselor', 'attachments', 'histories.actor', 'bkRecord']);

        return view('admin.kesiswaan.referral', compact('referral'));
    }

    public function attachment(StudentReferral $referral, StudentReferralAttachment $attachment)
    {
        abort_unless($attachment->student_referral_id === $referral->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name, ['Content-Type' => $attachment->mime_type]);
    }
}
