<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentReferralRequest;
use App\Http\Requests\TransitionStudentReferralRequest;
use App\Http\Resources\StudentReferralResource;
use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\StudentReferralAttachment;
use App\Services\Kesiswaan\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Rujukan siswa untuk klien native.
 *
 * Alur lengkap ada di ReferralService (transaksi, kunci baris saat claim,
 * pembersihan lampiran bila gagal). Controller ini hanya lapis otorisasi +
 * bentuk JSON, jadi aturan bisnis tidak pernah bercabang antara web dan API.
 */
class StudentReferralController extends Controller
{
    private const PER_PAGE = 15;

    /**
     * Rujukan yang dibuat pemanggil sendiri ("Rujukan Saya").
     */
    public function mine(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()->activeHomeroomAssignment() !== null,
            403,
            'Anda tidak memiliki penugasan wali kelas aktif.'
        );

        $referrals = StudentReferral::query()
            ->where('created_by', $request->user()->id)
            ->with(['student.schoolClass:id,name', 'counselor:id,name'])
            ->withCount('attachments')
            ->latest()
            ->paginate($this->perPage($request));

        return $this->paged($referrals);
    }

    /**
     * Antrean Guru BK: rujukan baru di jenjangnya + rujukan yang sudah diambilnya.
     *
     * Urutan mendesak → penting → normal, lalu tertua dulu; menyamai antrean web
     * supaya dua klien tidak memberi prioritas berbeda pada kasus yang sama.
     */
    public function queue(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user->is_bk_counselor && in_array($user->office?->school_level, Student::LEVELS, true),
            403,
            'Anda tidak memiliki akses antrean Guru BK.'
        );

        $referrals = StudentReferral::visibleTo($user)
            ->with(['student.schoolClass:id,name', 'creator:id,name', 'counselor:id,name'])
            ->withCount('attachments')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByRaw("CASE urgency WHEN 'urgent' THEN 1 WHEN 'important' THEN 2 ELSE 3 END")
            ->oldest()
            ->paginate($this->perPage($request));

        return $this->paged($referrals);
    }

    public function show(StudentReferral $referral): JsonResponse
    {
        Gate::authorize('view', $referral);

        $referral->load([
            'student.schoolClass:id,name',
            'creator:id,name',
            'counselor:id,name',
            'attachments',
            'histories.actor:id,name',
            'bkRecord:id,student_referral_id',
        ]);

        return response()->json(['data' => new StudentReferralResource($referral)]);
    }

    /**
     * Buat rujukan untuk seorang siswa di kelas yang diampu pemanggil.
     */
    public function store(
        StoreStudentReferralRequest $request,
        Student $student,
        ReferralService $service,
    ): JsonResponse {
        $this->authorizeHomeroomStudent($request, $student);

        $referral = $service->create(
            $student,
            $request->user(),
            $request->safe()->except('attachments'),
            $request->file('attachments', []),
        );

        $referral->load(['student.schoolClass:id,name', 'creator:id,name', 'attachments', 'histories.actor:id,name']);

        return response()->json(['data' => new StudentReferralResource($referral)], 201);
    }

    public function claim(Request $request, StudentReferral $referral, ReferralService $service): JsonResponse
    {
        Gate::authorize('claim', $referral);
        $service->claim($referral, $request->user());

        return $this->fresh($referral);
    }

    public function transition(
        TransitionStudentReferralRequest $request,
        StudentReferral $referral,
        ReferralService $service,
    ): JsonResponse {
        $service->transition(
            $referral,
            $request->user(),
            $request->validated('status'),
            $request->validated('safe_summary'),
        );

        return $this->fresh($referral);
    }

    /**
     * Lampiran disajikan lewat route ber-auth, bukan URL storage publik.
     */
    public function attachment(StudentReferral $referral, StudentReferralAttachment $attachment): StreamedResponse
    {
        Gate::authorize('view', $referral);
        abort_unless($attachment->student_referral_id === $referral->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download(
            $attachment->path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type],
        );
    }

    /**
     * Metadata untuk membangun form rujukan tanpa menyalin konstanta ke klien.
     */
    public function meta(Request $request): JsonResponse
    {
        $user = $request->user();
        $assignment = $user->activeHomeroomAssignment();

        return response()->json([
            'school_level' => $user->office?->school_level,
            'is_homeroom_teacher' => $assignment !== null,
            'homeroom_class' => $assignment?->schoolClass?->name,
            'is_bk_counselor' => (bool) $user->is_bk_counselor,
            'urgencies' => ['normal', 'important', 'urgent'],
            'urgency_labels' => ['normal' => 'Normal', 'important' => 'Penting', 'urgent' => 'Mendesak'],
            'statuses' => ['new', 'in_handling', 'completed', 'rejected'],
            'status_labels' => ['new' => 'Baru', 'in_handling' => 'Ditangani', 'completed' => 'Selesai', 'rejected' => 'Ditolak'],
            'limits' => [
                'max_attachments' => 3,
                'max_attachment_kb' => 5120,
                'attachment_mimes' => ['jpg', 'jpeg', 'png', 'pdf'],
            ],
        ]);
    }

    private function fresh(StudentReferral $referral): JsonResponse
    {
        $referral->refresh()->load([
            'student.schoolClass:id,name',
            'creator:id,name',
            'counselor:id,name',
            'attachments',
            'histories.actor:id,name',
        ]);

        return response()->json(['data' => new StudentReferralResource($referral)]);
    }

    /**
     * @param  \Illuminate\Pagination\LengthAwarePaginator<int, StudentReferral>  $page
     */
    private function paged($page): JsonResponse
    {
        return response()->json([
            'data' => StudentReferralResource::collection($page->items()),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    /**
     * 404 (bukan 403) supaya keberadaan siswa di kelas lain tidak bocor.
     */
    private function authorizeHomeroomStudent(Request $request, Student $student): void
    {
        $assignment = $request->user()->activeHomeroomAssignment();

        abort_unless(
            $assignment
                && $student->status === 'Aktif'
                && $student->school_class_id === $assignment->school_class_id,
            404
        );
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', self::PER_PAGE)));
    }
}
