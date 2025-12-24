<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AttendanceController - View and manage all attendance records.
 */
class AttendanceController extends Controller
{
    /**
     * Display a listing of all attendance records.
     */
    public function index(Request $request): View
    {
        $query = Attendance::with(['user', 'user.office']);

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by office
        if ($request->filled('office_id')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('office_id', $request->office_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->latest()->paginate(20)->withQueryString();
        $offices = Office::orderBy('name')->get();

        return view('admin.attendances.index', [
            'attendances' => $attendances,
            'offices' => $offices,
        ]);
    }

    /**
     * Show the specified attendance record.
     */
    public function show(Attendance $attendance): View
    {
        $attendance->load(['user', 'user.office']);

        return view('admin.attendances.show', [
            'attendance' => $attendance,
        ]);
    }
}
