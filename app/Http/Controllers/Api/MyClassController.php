<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeroomViolationResource;
use App\Http\Resources\StudentResource;
use App\Models\HomeroomAssignment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * "Kelas Saya" untuk klien native — cermin dari Employee\MyClassController.
 *
 * Semua query di sini ditambatkan ke `school_class_id` milik penugasan aktif,
 * jadi id siswa dari klien tidak pernah bisa menunjuk kelas lain.
 */
class MyClassController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Ringkasan kelas: identitas penugasan + hitungan yang dipakai kartu dashboard.
     */
    public function index(Request $request): JsonResponse
    {
        $assignment = $this->assignment($request);

        $students = $this->students($assignment)
            ->when(
                $request->filled('search'),
                fn (Builder $query) => $query->where('nama_lengkap', 'like', '%'.$request->string('search').'%')
            )
            ->withCount(['bkRecords as violations_count' => fn (Builder $query) => $query
                ->where('record_type', 'violation')
                ->whereNull('archived_at')])
            ->orderBy('nama_lengkap')
            ->paginate($this->perPage($request));

        return response()->json([
            'assignment' => [
                'class_name' => $assignment->schoolClass?->name,
                'school_level' => $assignment->schoolClass?->school_level,
                'academic_year' => $assignment->academicYear?->name,
            ],
            'summary' => [
                'student_count' => $this->students($assignment)->count(),
                'students_with_violations' => $this->students($assignment)
                    ->whereHas('bkRecords', fn (Builder $query) => $query
                        ->where('record_type', 'violation')
                        ->whereNull('archived_at'))
                    ->count(),
            ],
            'data' => StudentResource::collection($students->items()),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    /**
     * Profil satu siswa di kelas ini, beserta ringkasan pelanggaran yang aman
     * untuk wali kelas. Catatan konseling tidak pernah masuk ke sini.
     */
    public function show(Request $request, Student $student): JsonResponse
    {
        $assignment = $this->assignment($request);
        abort_unless($student->school_class_id === $assignment->school_class_id, 403);

        $student->loadMissing('schoolClass');

        $violations = $student->bkRecords()
            ->where('record_type', 'violation')
            ->whereNull('archived_at')
            ->with('category:id,name')
            ->latest('occurred_at')
            ->get();

        return response()->json([
            'student' => new StudentResource($student, true),
            'violations' => HomeroomViolationResource::collection($violations),
            'can_create_referral' => $student->status === 'Aktif',
        ]);
    }

    /**
     * Ringkasan BK yang aman: hitungan dan jenis saja, tanpa isi profesional.
     *
     * Wali kelas memerlukan sinyal "perlu perhatian" tanpa berhak membaca catatan
     * konseling, jadi ringkasan dihitung dari sudut pandang siswa — bukan dari
     * sudut pandang konselor seperti BkSummaryService yang memfilter kepemilikan.
     */
    public function bkSummary(Request $request, Student $student): JsonResponse
    {
        $assignment = $this->assignment($request);
        abort_unless($student->school_class_id === $assignment->school_class_id, 403);

        $records = $student->bkRecords()->whereNull('archived_at');

        return response()->json([
            'active_count' => (clone $records)->where('status', '!=', 'completed')->count(),
            'types' => (clone $records)->distinct()->pluck('record_type')->all(),
            'statuses' => (clone $records)->distinct()->pluck('status')->all(),
            'needs_follow_up' => (clone $records)
                ->where(fn (Builder $query) => $query
                    ->whereIn('status', ['in_progress', 'waiting_follow_up'])
                    ->orWhereNotNull('next_follow_up_at'))
                ->exists(),
        ]);
    }

    /**
     * @return Builder<Student>
     */
    private function students(HomeroomAssignment $assignment): Builder
    {
        return Student::query()
            ->where('school_class_id', $assignment->school_class_id)
            ->where('status', 'Aktif');
    }

    private function assignment(Request $request): HomeroomAssignment
    {
        $assignment = $request->attributes->get('homeroom_assignment')
            ?? $request->user()->activeHomeroomAssignment();

        abort_unless($assignment, 403, 'Anda tidak memiliki penugasan wali kelas aktif.');

        return $assignment;
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', self::PER_PAGE)));
    }
}
