<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BkRecord;
use App\Models\HomeroomAssignment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MyClassController extends Controller
{
    public function index(Request $request): View
    {
        $assignment = $this->assignment($request);
        $students = Student::query()->where('school_class_id', $assignment->school_class_id)
            ->where('status', 'Aktif')
            ->when($request->filled('search'), fn ($query) => $query->where('nama_lengkap', 'like', '%'.$request->string('search').'%'))
            ->withCount(['bkRecords as violations_count' => fn ($query) => $query->where('record_type', 'violation')->whereNull('archived_at')])
            ->orderBy('nama_lengkap')->paginate(20)->withQueryString();

        return view('attendance.my-class.index', compact('assignment', 'students'));
    }

    public function show(Request $request, Student $student): View
    {
        $assignment = $this->assignment($request);
        abort_unless($student->school_class_id === $assignment->school_class_id, 403);
        $violations = $student->bkRecords()->where('record_type', 'violation')->whereNull('archived_at')->with('category')->latest('occurred_at')->get();

        return view('attendance.my-class.show', compact('assignment', 'student', 'violations'));
    }

    public function violation(Request $request, BkRecord $record): View
    {
        $assignment = $this->assignment($request);
        abort_unless($record->record_type === 'violation' && $record->student?->school_class_id === $assignment->school_class_id, 403);
        $record->load(['student', 'category', 'counselor']);

        return view('attendance.my-class.violation', compact('assignment', 'record'));
    }

    private function assignment(Request $request): HomeroomAssignment
    {
        $assignment = $request->user()->activeHomeroomAssignment();
        abort_unless($assignment, 403, 'Anda tidak memiliki penugasan wali kelas aktif.');

        return $assignment;
    }
}
