<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(string $schoolLevel): View
    {
        $this->assertLevel($schoolLevel);
        $classes = SchoolClass::query()->where('school_level', $schoolLevel)->withCount('students')->orderBy('grade_level')->orderBy('name')->paginate(15);

        return view('admin.school-classes.index', ['classes' => $classes, 'schoolLevel' => $schoolLevel]);
    }

    public function create(string $schoolLevel): View
    {
        $this->assertLevel($schoolLevel);

        return view('admin.school-classes.create', ['schoolLevel' => $schoolLevel]);
    }

    public function store(StoreSchoolClassRequest $request, string $schoolLevel): RedirectResponse
    {
        $this->assertLevel($schoolLevel);
        SchoolClass::query()->create([...$request->validated(), 'school_level' => $schoolLevel, 'is_active' => $request->boolean('is_active')]);

        return to_route('admin.school-classes.index', $schoolLevel)->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(string $schoolLevel, SchoolClass $schoolClass): View
    {
        $this->assertClassLevel($schoolLevel, $schoolClass);

        $activeYear = AcademicYear::getActive();
        $assignment = $activeYear ? $schoolClass->homeroomAssignments()->where('academic_year_id', $activeYear->id)->first() : null;
        $teachers = User::query()
            ->whereHas('role', fn ($query) => $query->where('slug', 'guru')->where('is_admin', false))
            ->whereHas('office', fn ($query) => $query->where('school_level', $schoolLevel))
            ->with('office')->orderBy('name')->get();

        return view('admin.school-classes.edit', compact('schoolClass', 'schoolLevel', 'activeYear', 'assignment', 'teachers'));
    }

    public function update(UpdateSchoolClassRequest $request, string $schoolLevel, SchoolClass $schoolClass): RedirectResponse
    {
        $this->assertClassLevel($schoolLevel, $schoolClass);
        $data = $request->validated();
        $teacherId = $data['teacher_id'] ?? null;
        unset($data['teacher_id']);

        DB::transaction(function () use ($request, $schoolClass, $data, $teacherId): void {
            $schoolClass->update([...$data, 'is_active' => $request->boolean('is_active')]);

            $activeYear = AcademicYear::getActive();
            if ($activeYear && $teacherId) {
                HomeroomAssignment::query()->updateOrCreate(
                    ['academic_year_id' => $activeYear->id, 'school_class_id' => $schoolClass->id],
                    ['teacher_id' => $teacherId, 'assigned_by' => $request->user()->id],
                );
            }
        });

        return to_route('admin.school-classes.index', $schoolLevel)->with('success', 'Kelas dan wali kelas berhasil diperbarui.');
    }

    public function destroy(string $schoolLevel, SchoolClass $schoolClass): RedirectResponse
    {
        $this->assertClassLevel($schoolLevel, $schoolClass);
        if ($schoolClass->students()->exists()) {
            return back()->with('error', 'Kelas masih memiliki siswa dan tidak dapat dihapus.');
        }
        $schoolClass->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    private function assertLevel(string $level): void
    {
        abort_unless(in_array($level, Student::LEVELS, true), 404);
    }

    private function assertClassLevel(string $level, SchoolClass $class): void
    {
        $this->assertLevel($level);
        abort_unless($class->school_level === $level, 404);
    }
}
