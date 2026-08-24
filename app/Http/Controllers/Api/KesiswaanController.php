<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentReferralResource;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Services\Kesiswaan\BkSummaryService;
use App\Services\Kesiswaan\StudentAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Direktori Kesiswaan hanya-baca untuk Petugas Kesiswaan yang ditunjuk.
 *
 * Cakupan MI/SMP diambil dari StudentAccessService — sumber kebenaran yang sama
 * dengan PWA — sehingga pemetaan office→jenjang tidak pernah bercabang dua.
 */
class KesiswaanController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request, StudentAccessService $access): JsonResponse
    {
        $students = $access->query($request->user())
            ->with('schoolClass:id,name')
            ->when($request->filled('search'), fn (Builder $query) => $query->where(
                fn (Builder $inner) => $inner
                    ->where('nama_lengkap', 'like', '%'.$request->string('search').'%')
                    ->orWhere('nisn', 'like', '%'.$request->string('search').'%')
                    ->orWhere('nik', 'like', '%'.$request->string('search').'%')
            ))
            ->orderBy('nama_lengkap')
            ->paginate($this->perPage($request));

        return response()->json([
            'scope' => ['school_level' => $request->user()->office?->school_level],
            'data' => StudentResource::collection($students->items()),
            'meta' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'total' => $students->total(),
            ],
        ]);
    }

    /**
     * Profil siswa + ringkasan BK yang aman + rujukan yang boleh dilihat pemanggil.
     *
     * Ringkasan BK sengaja dihitung oleh BkSummaryService dari sudut pandang
     * pemanggil, jadi petugas tidak pernah menerima isi catatan profesional.
     */
    public function show(
        Request $request,
        Student $student,
        StudentAccessService $access,
        BkSummaryService $summaries,
    ): JsonResponse {
        $access->authorize($request->user(), $student);

        $student->loadMissing([
            'schoolClass.homeroomAssignments' => fn ($query) => $query
                ->with(['teacher:id,name', 'academicYear:id,name,is_active'])
                ->whereHas('academicYear', fn ($inner) => $inner->where('is_active', true)),
        ]);

        $homeroom = $student->schoolClass?->homeroomAssignments->first();

        $referrals = StudentReferral::visibleTo($request->user())
            ->where('student_id', $student->id)
            ->with(['creator:id,name', 'counselor:id,name'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'student' => new StudentResource($student, true),
            'homeroom_teacher' => $homeroom?->teacher?->name,
            'academic_year' => $homeroom?->academicYear?->name,
            'bk_summary' => $summaries->for($student, $request->user()),
            'referrals' => StudentReferralResource::collection($referrals->items()),
            'referrals_meta' => [
                'current_page' => $referrals->currentPage(),
                'last_page' => $referrals->lastPage(),
                'total' => $referrals->total(),
            ],
        ]);
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', self::PER_PAGE)));
    }
}
