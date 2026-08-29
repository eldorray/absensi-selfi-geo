<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\StudentSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use RuntimeException;

class StudentController extends Controller
{
    public function index(Request $request, string $schoolLevel): View
    {
        $this->assertLevel($schoolLevel);
        $students = Student::query()->with('schoolClass')->where('school_level', $schoolLevel)
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($nested) => $nested->where('nama_lengkap', 'like', '%'.$request->string('search').'%')->orWhere('nisn', 'like', '%'.$request->string('search').'%')->orWhere('nik', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('school_class_id'), fn ($query) => $query->where('school_class_id', $request->integer('school_class_id')))
            ->orderBy('nama_lengkap')->paginate(15)->withQueryString();

        return view('admin.students.index', ['students' => $students, 'classes' => $this->classes($schoolLevel), 'schoolLevel' => $schoolLevel]);
    }

    public function create(string $schoolLevel): View
    {
        $this->assertLevel($schoolLevel);

        return view('admin.students.create', ['classes' => $this->classes($schoolLevel), 'schoolLevel' => $schoolLevel]);
    }

    public function store(StoreStudentRequest $request, string $schoolLevel): RedirectResponse
    {
        $this->assertLevel($schoolLevel);
        Student::query()->create($this->studentAttributes($request->validated(), $schoolLevel));

        return to_route('admin.students.index', $schoolLevel)->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(string $schoolLevel, Student $student): View
    {
        $this->assertStudentLevel($schoolLevel, $student);

        return view('admin.students.edit', ['student' => $student, 'classes' => $this->classes($schoolLevel), 'schoolLevel' => $schoolLevel]);
    }

    public function update(UpdateStudentRequest $request, string $schoolLevel, Student $student): RedirectResponse
    {
        $this->assertStudentLevel($schoolLevel, $student);
        $student->update($this->studentAttributes($request->validated(), $schoolLevel, $student));

        return to_route('admin.students.index', $schoolLevel)->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(string $schoolLevel, Student $student): RedirectResponse
    {
        $this->assertStudentLevel($schoolLevel, $student);
        $student->delete();

        return to_route('admin.students.index', $schoolLevel)->with('success', 'Data siswa berhasil dihapus.');
    }

    public function bulkDestroy(Request $request, string $schoolLevel): RedirectResponse
    {
        $this->assertLevel($schoolLevel);
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        // Siswa yang punya catatan BK atau rujukan ditahan oleh foreign key
        // restrict; lewati supaya satu baris terkunci tidak menggagalkan seluruh batch.
        $students = Student::query()->where('school_level', $schoolLevel)->whereIn('id', $ids)->get();
        $deletable = $students->reject(fn (Student $student) => $student->bkRecords()->exists() || $student->referrals()->exists());
        $blocked = $students->count() - $deletable->count();

        Student::query()->whereIn('id', $deletable->modelKeys())->delete();

        $message = $deletable->count().' siswa berhasil dihapus.';
        if ($blocked > 0) {
            $message .= ' '.$blocked.' siswa dilewati karena masih punya catatan BK atau rujukan.';
        }

        return to_route('admin.students.index', $schoolLevel)->with('success', $message);
    }

    public function sync(string $schoolLevel, StudentSyncService $service): RedirectResponse
    {
        $this->assertLevel($schoolLevel);
        try {
            $result = $service->sync($schoolLevel);
        } catch (ConnectionException $exception) {
            Log::error('Student sync connection failed', ['level' => $schoolLevel, 'message' => $exception->getMessage()]);

            return back()->with('error', 'Tidak dapat terhubung ke API data induk.');
        } catch (RuntimeException $exception) {
            Log::error('Student sync failed', ['level' => $schoolLevel, 'message' => $exception->getMessage()]);

            return back()->with('error', 'Sinkronisasi gagal. Periksa respons API data induk.');
        }

        return back()->with('success', "Sinkronisasi selesai: {$result['created']} dibuat, {$result['updated']} diperbarui, {$result['skipped']} dilewati.");
    }

    private function classes(string $level)
    {
        return SchoolClass::query()->where('school_level', $level)->where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
    }

    private function studentAttributes(array $data, string $level, ?Student $student = null): array
    {
        $class = isset($data['school_class_id']) ? SchoolClass::query()->find($data['school_class_id']) : null;

        return [...$data, 'school_level' => $level, 'source' => $student?->source ?? 'manual', 'tingkat_rombel' => $class?->name];
    }

    private function assertLevel(string $level): void
    {
        abort_unless(in_array($level, Student::LEVELS, true), 404);
    }

    private function assertStudentLevel(string $level, Student $student): void
    {
        $this->assertLevel($level);
        abort_unless($student->school_level === $level, 404);
    }
}
