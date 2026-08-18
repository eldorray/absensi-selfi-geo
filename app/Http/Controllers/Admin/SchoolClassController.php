<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSchoolClassRequest;
use App\Http\Requests\Admin\UpdateSchoolClassRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
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

        return view('admin.school-classes.edit', ['schoolClass' => $schoolClass, 'schoolLevel' => $schoolLevel]);
    }

    public function update(UpdateSchoolClassRequest $request, string $schoolLevel, SchoolClass $schoolClass): RedirectResponse
    {
        $this->assertClassLevel($schoolLevel, $schoolClass);
        $schoolClass->update([...$request->validated(), 'is_active' => $request->boolean('is_active')]);

        return to_route('admin.school-classes.index', $schoolLevel)->with('success', 'Kelas berhasil diperbarui.');
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
