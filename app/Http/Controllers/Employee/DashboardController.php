<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * DashboardController - Handle employee dashboard display.
 * 
 * Follows Single Responsibility Principle by only handling dashboard operations.
 */
class DashboardController extends Controller
{
    /**
     * Display the employee dashboard with attendance summary.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        // Get today's work schedule
        $todayDay = strtolower(now()->locale('id')->dayName);
        $todaySchedule = WorkSchedule::where('user_id', $user->id)
            ->where('day', $todayDay)
            ->where('is_active', true)
            ->first();

        // Monthly stats - hadir includes both present and late
        $monthStart = now()->startOfMonth();
        $monthlyPresent = Attendance::where('user_id', $user->id)
            ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
            ->whereBetween('created_at', [$monthStart, now()])
            ->count();

        $monthlyLate = Attendance::where('user_id', $user->id)
            ->where('status', AttendanceStatus::Late)
            ->whereBetween('created_at', [$monthStart, now()])
            ->count();

        $totalAttendance = $monthlyPresent;

        return view('attendance.dashboard', [
            'todayAttendance' => $todayAttendance,
            'todaySchedule' => $todaySchedule,
            'monthlyPresent' => $monthlyPresent,
            'monthlyLate' => $monthlyLate,
            'totalAttendance' => $totalAttendance,
        ]);
    }
}
