<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertHomeroomAssignmentRequest;
use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeroomAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $year = AcademicYear::query()->find($request->integer('academic_year_id')) ?? AcademicYear::getActive() ?? AcademicYear::query()->latest('start_date')->first();
        $level = in_array($request->string('school_level')->value(), Student::LEVELS, true) ? $request->string('school_level')->value() : 'mi';
        $classes = SchoolClass::query()->where('school_level', $level)->withCount(['students' => fn ($query) => $query->where('status', 'Aktif')])->with(['homeroomAssignments' => fn ($query) => $query->where('academic_year_id', $year?->id)->with('teacher.office')])->orderBy('grade_level')->orderBy('name')->get();
        $previousYear = $year ? AcademicYear::query()->where('start_date', '<', $year->start_date)->latest('start_date')->first() : null;
        $copyPreview = $request->boolean('preview_copy') && $year && $previousYear
            ? $this->copyPreview($year, $previousYear)
            : collect();

        return view('admin.homeroom-assignments.index', [
            'academicYears' => AcademicYear::query()->latest('start_date')->get(),
            'assignments' => HomeroomAssignment::query()->where('academic_year_id', $year?->id)->whereHas('schoolClass', fn ($query) => $query->where('school_level', $level))->with(['schoolClass', 'teacher.office'])->get(),
            'classes' => $classes,
            'teachers' => $this->teachers($level),
            'selectedYear' => $year,
            'schoolLevel' => $level,
            'previousYear' => $previousYear,
            'copyPreview' => $copyPreview,
        ]);
    }

    public function store(UpsertHomeroomAssignmentRequest $request): RedirectResponse
    {
        HomeroomAssignment::query()->create([...$request->validated(), 'assigned_by' => $request->user()->id]);

        return back()->with('success', 'Wali kelas berhasil ditetapkan.');
    }

    public function update(UpsertHomeroomAssignmentRequest $request, HomeroomAssignment $homeroomAssignment): RedirectResponse
    {
        $homeroomAssignment->update([...$request->validated(), 'assigned_by' => $request->user()->id]);

        return back()->with('success', 'Penugasan wali kelas berhasil diperbarui.');
    }

    public function destroy(HomeroomAssignment $homeroomAssignment): RedirectResponse
    {
        $homeroomAssignment->delete();

        return back()->with('success', 'Penugasan wali kelas berhasil dihapus.');
    }

    public function copyPrevious(Request $request): RedirectResponse
    {
        $data = $request->validate(['academic_year_id' => ['required', 'exists:academic_years,id']]);
        $target = AcademicYear::query()->findOrFail($data['academic_year_id']);
        $previous = AcademicYear::query()->where('start_date', '<', $target->start_date)->latest('start_date')->first();

        if (! $previous) {
            return back()->with('error', 'Tahun ajaran sebelumnya tidak ditemukan.');
        }

        $existingClasses = HomeroomAssignment::query()->where('academic_year_id', $target->id)->pluck('school_class_id');
        $existingTeachers = HomeroomAssignment::query()->where('academic_year_id', $target->id)->pluck('teacher_id');
        $copied = 0;

        DB::transaction(function () use ($request, $target, $previous, $existingClasses, $existingTeachers, &$copied): void {
            $source = HomeroomAssignment::query()->where('academic_year_id', $previous->id)->with(['schoolClass', 'teacher.role', 'teacher.office'])->get();
            foreach ($source as $assignment) {
                $valid = $assignment->schoolClass->is_active
                    && $assignment->teacher->role?->slug === 'guru'
                    && $assignment->teacher->office?->school_level === $assignment->schoolClass->school_level
                    && ! $existingClasses->contains($assignment->school_class_id)
                    && ! $existingTeachers->contains($assignment->teacher_id);
                if ($valid) {
                    HomeroomAssignment::query()->create(['academic_year_id' => $target->id, 'school_class_id' => $assignment->school_class_id, 'teacher_id' => $assignment->teacher_id, 'assigned_by' => $request->user()->id]);
                    $existingClasses->push($assignment->school_class_id);
                    $existingTeachers->push($assignment->teacher_id);
                    $copied++;
                }
            }
        });

        return back()->with('success', "{$copied} penugasan valid berhasil disalin. Konflik dan data tidak valid dilewati.");
    }

    private function teachers(string $level)
    {
        return User::query()->whereHas('role', fn ($query) => $query->where('slug', 'guru')->where('is_admin', false))->whereHas('office', fn ($query) => $query->where('school_level', $level))->with('office')->orderBy('name')->get();
    }

    private function copyPreview(AcademicYear $target, AcademicYear $previous)
    {
        $existingClasses = HomeroomAssignment::query()->where('academic_year_id', $target->id)->pluck('school_class_id');
        $existingTeachers = HomeroomAssignment::query()->where('academic_year_id', $target->id)->pluck('teacher_id');

        return HomeroomAssignment::query()->where('academic_year_id', $previous->id)
            ->with(['schoolClass', 'teacher.role', 'teacher.office'])->get()
            ->map(function (HomeroomAssignment $assignment) use ($existingClasses, $existingTeachers): array {
                $reason = match (true) {
                    ! $assignment->schoolClass->is_active => 'Kelas tidak aktif',
                    $assignment->teacher->role?->slug !== 'guru' => 'Pengguna bukan Guru',
                    $assignment->teacher->office?->school_level !== $assignment->schoolClass->school_level => 'Jenjang kantor tidak sesuai',
                    $existingClasses->contains($assignment->school_class_id) => 'Kelas tujuan sudah memiliki wali',
                    $existingTeachers->contains($assignment->teacher_id) => 'Guru sudah memegang kelas lain',
                    default => null,
                };

                return ['assignment' => $assignment, 'valid' => $reason === null, 'reason' => $reason];
            });
    }
}
