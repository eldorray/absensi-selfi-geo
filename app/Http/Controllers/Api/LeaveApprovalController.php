<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RejectLeaveRequest;
use App\Http\Resources\LeaveResource;
use App\Models\Leave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Persetujuan perizinan untuk Admin / Kepala Sekolah.
 *
 * Kelayakan penyetuju ditegakkan middleware `can-approve-leave`, bukan di sini,
 * supaya aturan siapa boleh menyetujui tetap satu tempat untuk web dan API.
 */
class LeaveApprovalController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): JsonResponse
    {
        $page = Leave::query()
            ->with(['user:id,name,office_id', 'user.office:id,name', 'approver:id,name'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->orderByDesc('created_at')
            ->paginate($this->perPage($request));

        return response()->json([
            'pending_count' => Leave::pending()->count(),
            'data' => collect($page->items())->map(fn (Leave $leave): array => $this->row($leave))->all(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function show(Leave $leave): JsonResponse
    {
        $leave->load(['user:id,name,email,office_id,role_id', 'user.office:id,name', 'user.role:id,name', 'approver:id,name']);

        return response()->json([
            'data' => $this->row($leave) + [
                'applicant_email' => $leave->user?->email,
                'applicant_role' => $leave->user?->role?->name,
                'approver_name' => $leave->approver?->name,
                'approved_at' => $leave->approved_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Menyetujui pengajuan. Pengajuan yang sudah diproses ditolak dengan 422 —
     * bukan 200 diam-diam — supaya klien tidak menampilkan sukses palsu ketika
     * dua penyetuju menekan tombol pada saat hampir bersamaan.
     */
    public function approve(Request $request, Leave $leave): JsonResponse
    {
        $this->ensurePending($leave);

        $leave->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Pengajuan perizinan berhasil disetujui.',
            'data' => new LeaveResource($leave->fresh()),
        ]);
    }

    public function reject(RejectLeaveRequest $request, Leave $leave): JsonResponse
    {
        $this->ensurePending($leave);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => $request->validated('rejection_reason'),
        ]);

        return response()->json([
            'message' => 'Pengajuan perizinan ditolak.',
            'data' => new LeaveResource($leave->fresh()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Leave $leave): array
    {
        return (new LeaveResource($leave))->toArray(request()) + [
            'applicant_name' => $leave->user?->name,
            'applicant_office' => $leave->user?->office?->name,
        ];
    }

    private function ensurePending(Leave $leave): void
    {
        abort_if($leave->status !== 'pending', 422, 'Pengajuan ini sudah diproses.');
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', self::PER_PAGE)));
    }
}
