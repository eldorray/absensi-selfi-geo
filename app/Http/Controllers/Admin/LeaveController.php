<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Leave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * LeaveController - Admin leave approval management.
 */
class LeaveController extends Controller
{
    /**
     * Display the leave queue: list on the left, the selected request on the right.
     * Without a status filter the queue opens on pending requests; `status=all`
     * lists everything.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status', 'pending');
        $status = in_array($status, ['pending', 'approved', 'rejected', 'all'], true) ? $status : 'pending';
        $type = in_array($request->input('type'), ['izin', 'sakit', 'cuti'], true) ? $request->input('type') : null;

        $leaves = Leave::with(['user', 'user.role', 'user.office'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $selected = $request->filled('leave')
            ? Leave::find($request->integer('leave'))
            : $leaves->first();
        $selected?->load(['user', 'user.role', 'user.office', 'approver']);

        return view('admin.leaves.index', [
            'leaves' => $leaves,
            'status' => $status,
            'type' => $type,
            'selected' => $selected,
            'history' => $selected ? $this->approvedDaysThisYear($selected) : [],
            'pendingCount' => Leave::pending()->count(),
        ]);
    }

    /**
     * Show leave detail.
     */
    public function show(Leave $leave): View
    {
        $leave->load(['user', 'user.role', 'user.office', 'approver']);

        return view('admin.leaves.show', [
            'leave' => $leave,
            'history' => $this->approvedDaysThisYear($leave),
        ]);
    }

    /**
     * Approve leave request.
     */
    public function approve(Leave $leave): RedirectResponse
    {
        if (! $leave->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $leave->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan perizinan berhasil disetujui.');
    }

    /**
     * Reject leave request.
     */
    public function reject(Request $request, Leave $leave): RedirectResponse
    {
        if (! $leave->isPending()) {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Pengajuan perizinan ditolak.');
    }

    /**
     * Approved leave days per type for the requester in the active academic
     * year (calendar year when none is active), shown next to the request so
     * the approver sees the pattern without opening another page.
     *
     * @return array<string, int>
     */
    private function approvedDaysThisYear(Leave $leave): array
    {
        $year = AcademicYear::getActive();
        $from = $year->start_date ?? now()->startOfYear();
        $to = $year->end_date ?? now()->endOfYear();

        $days = Leave::query()
            ->where('user_id', $leave->user_id)
            ->where('status', 'approved')
            ->whereDate('start_date', '>=', $from)
            ->whereDate('start_date', '<=', $to)
            ->get()
            ->groupBy('type')
            ->map(fn ($group): int => (int) $group->sum(fn (Leave $l): int => $l->duration));

        return [
            'izin' => $days->get('izin', 0),
            'sakit' => $days->get('sakit', 0),
            'cuti' => $days->get('cuti', 0),
        ];
    }
}
