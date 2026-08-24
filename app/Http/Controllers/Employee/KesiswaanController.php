<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Services\Kesiswaan\BkSummaryService;
use App\Services\Kesiswaan\StudentAccessService;
use Illuminate\Http\Request;

class KesiswaanController extends Controller
{
    public function index(Request $r, StudentAccessService $a)
    {
        $students = $a->query($r->user())->with('schoolClass')->when($r->filled('search'), fn ($q) => $q->where(fn ($x) => $x->where('nama_lengkap', 'like', '%'.$r->search.'%')->orWhere('nisn', 'like', '%'.$r->search.'%')->orWhere('nik', 'like', '%'.$r->search.'%')))->orderBy('nama_lengkap')->paginate(20)->withQueryString()->fragment('student-directory');

        return view('attendance.kesiswaan.index', compact('students'));
    }

    public function show(Request $r, Student $student, StudentAccessService $a, BkSummaryService $b)
    {
        $a->authorize($r->user(), $student);
        $student->load(['schoolClass.homeroomAssignments' => fn ($q) => $q->with(['teacher', 'academicYear'])->whereHas('academicYear', fn ($x) => $x->where('is_active', true))]);
        $summary = $b->for($student, $r->user());
        $referrals = StudentReferral::visibleTo($r->user())->where('student_id', $student->id)->with(['creator', 'counselor'])->latest()->paginate(10);

        return view('attendance.kesiswaan.show', compact('student', 'summary', 'referrals'));
    }
}
