<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\EmployeeDashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * DashboardController - Handle employee dashboard display.
 *
 * Follows Single Responsibility Principle by only handling dashboard operations.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly EmployeeDashboardService $dashboard,
    ) {}

    /**
     * Display the employee dashboard with attendance summary.
     */
    public function index(): View
    {
        $user = Auth::user();

        $data = $this->dashboard->for($user);
        $homeroomAssignment = $user->activeHomeroomAssignment();

        return view('attendance.dashboard', [
            'todayAttendance' => $data->todayAttendance,
            'todaySchedule' => $data->todaySchedule,
            'checkoutOpensAt' => $data->checkoutOpensAt,
            'checkoutTimeReached' => $data->checkoutTimeReached,
            'monthlyPresent' => $data->monthlyPresent,
            'monthlyLate' => $data->monthlyLate,
            'totalAttendance' => $data->monthlyTotal(),
            'announcements' => $data->announcements,
            'linkedAccounts' => $user->linkedAccounts()->with('office')->orderBy('name')->get(),
            'homeroomAssignment' => $homeroomAssignment,
            'homeroomStudentCount' => $homeroomAssignment?->schoolClass->students()->where('status', 'Aktif')->count() ?? 0,
            'homeroomViolationCount' => $homeroomAssignment?->schoolClass->students()->whereHas('bkRecords', fn ($query) => $query->where('record_type', 'violation')->whereNull('archived_at'))->count() ?? 0,
        ]);
    }
}
