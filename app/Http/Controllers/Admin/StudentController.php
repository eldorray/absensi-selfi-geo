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
    /** Nilai yang boleh dipakai pada query string per_page; 'semua' menampilkan seluruh baris. */
    private const PER_PAGE_OPTIONS = ['10', '25', '100', 'semua'];

    private const DEFAULT_PER_PAGE = '25';

    public function index(Request $request, string $schoolLevel): View
    {
        $this->assertLevel($schoolLevel);
        $students = Student::query()->with('schoolClass')->where('school_level', $schoolLevel)
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($nested) => $nested->where('nama_lengkap', 'like', '%'.$request->string('search').'%')->orWhere('nisn', 'like', '%'.$request->string('search').'%')->orWhere('nik', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('school_class_id'), fn ($query) => $query->where('school_class_id', $request->integer('school_class_id')))
            ->orderBy('nama_lengkap')->paginate($this->perPage($request))->withQueryString();

        return view('admin.students.index', ['students' => $students, 'classes' => $this->classes($schoolLevel), 'schoolLevel' => $schoolLevel, 'perPageOptions' => self::PER_PAGE_OPTIONS, 'perPage' => $request->input('per_page', self::DEFAULT_PER_PAGE)]);
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

        if ($this->isLockedByRelations($student)) {
            return back()->with('error', 'Siswa tidak dapat dihapus karena masih punya catatan BK atau rujukan.');
        }

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
        $deletable = $students->reject(fn (Student $student) => $this->isLockedByRelations($student));
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

    /**
     * bk_records dan student_referrals memakai foreign key restrict, jadi hapus
     * siswa seperti ini melempar QueryException (SQLSTATE 23000), bukan error validasi.
     */
    private function isLockedByRelations(Student $student): bool
    {
        return $student->bkRecords()->exists() || $student->referrals()->exists();
    }

    private function perPage(Request $request): int
    {
        $requested = (string) $request->input('per_page', self::DEFAULT_PER_PAGE);
        $requested = in_array($requested, self::PER_PAGE_OPTIONS, true) ? $requested : self::DEFAULT_PER_PAGE;

        // ponytail: 'semua' cukup dipetakan ke perPage besar, tidak perlu cabang
        // non-paginated tersendiri. Naikkan bila satu jenjang bisa lebih dari 100k siswa.
        return $requested === 'semua' ? 100000 : (int) $requested;
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
