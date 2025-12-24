<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
     * Display list of leave requests.
     */
    public function index(Request $request): View
    {
        $query = Leave::with(['user', 'user.role', 'approver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Count pending leaves
        $pendingCount = Leave::pending()->count();

        return view('admin.leaves.index', [
            'leaves' => $leaves,
            'pendingCount' => $pendingCount,
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
        ]);
    }

    /**
     * Approve leave request.
     */
    public function approve(Leave $leave): RedirectResponse
    {
        if (!$leave->isPending()) {
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
        if (!$leave->isPending()) {
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
}
