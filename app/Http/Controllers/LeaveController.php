<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * LeaveController - Handle employee leave/permission requests.
 */
class LeaveController extends Controller
{
    /**
     * Display leave request list for employee.
     */
    public function index(): View
    {
        $leaves = Leave::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('attendance.leaves.index', [
            'leaves' => $leaves,
        ]);
    }

    /**
     * Show form to create new leave request.
     */
    public function create(): View
    {
        return view('attendance.leaves.create');
    }

    /**
     * Store new leave request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:izin,cuti,sakit',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'type.required' => 'Jenis perizinan harus dipilih.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'reason.required' => 'Alasan harus diisi.',
            'attachment.image' => 'Lampiran harus berupa gambar.',
            'attachment.max' => 'Ukuran gambar maksimal 5MB.',
        ]);

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = sprintf(
                '%d_%s_%s.%s',
                Auth::id(),
                now()->format('Y-m-d'),
                Str::random(8),
                $file->getClientOriginalExtension()
            );
            $attachmentPath = $file->storeAs('leaves', $filename, 'public');
        }

        Leave::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('attendance.leaves.index')
            ->with('success', 'Pengajuan perizinan berhasil dikirim.');
    }

    /**
     * Show leave detail.
     */
    public function show(Leave $leave): View
    {
        // Ensure user can only view their own leaves
        if ($leave->user_id !== Auth::id()) {
            abort(403);
        }

        return view('attendance.leaves.show', [
            'leave' => $leave,
        ]);
    }

    /**
     * Display leave approval list (for Admin/Kepala Sekolah).
     */
    public function approvalIndex(Request $request): View
    {
        $query = Leave::with(['user', 'user.role', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaves = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $pendingCount = Leave::pending()->count();

        return view('attendance.leaves.approval-index', [
            'leaves' => $leaves,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Display leave detail for approval (for Admin/Kepala Sekolah).
     */
    public function approvalShow(Leave $leave): View
    {
        $leave->load(['user', 'user.role', 'user.office', 'approver']);

        return view('attendance.leaves.approval-show', [
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
