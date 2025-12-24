<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controller for generating attendance reports.
 */
class ReportController extends Controller
{
    /**
     * Display the daily attendance report.
     */
    public function daily(Request $request): View
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        
        // Get active academic year
        $activeYear = AcademicYear::getActive();
        
        // Get all users (employees)
        $users = User::with(['role', 'office', 'workSchedules'])
            ->whereHas('role', fn($q) => $q->where('is_admin', false))
            ->orderBy('name')
            ->get();

        // Get attendances for the selected date
        $attendances = Attendance::with('user')
            ->whereDate('created_at', $selectedDate)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->keyBy('user_id');

        // Build report data
        $reportData = $users->map(function ($user) use ($attendances, $selectedDate) {
            $attendance = $attendances->get($user->id);
            
            // Get Indonesian day name (lowercase)
            $dayName = strtolower($selectedDate->locale('id')->dayName);
            
            $workSchedule = $user->workSchedules
                ->where('day', $dayName)
                ->where('is_active', true)
                ->first();

            return [
                'user' => $user,
                'work_schedule' => $workSchedule,
                'attendance' => $attendance,
                'status' => $this->determineStatus($attendance, $workSchedule),
            ];
        });

        // Calculate statistics
        $stats = [
            'total_employees' => $users->count(),
            'checked_in' => $attendances->count(),
            'checked_out' => $attendances->filter(fn($a) => $a->check_out_at !== null)->count(),
        ];

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.reports.daily', compact(
            'reportData',
            'stats',
            'selectedDate',
            'activeYear',
            'academicYears'
        ));
    }

    /**
     * Export daily attendance report as PDF.
     */
    public function exportDailyPdf(Request $request): Response
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);
        
        // Get active academic year
        $activeYear = AcademicYear::getActive();
        
        // Get all users (employees)
        $users = User::with(['role', 'office', 'workSchedules'])
            ->whereHas('role', fn($q) => $q->where('is_admin', false))
            ->orderBy('name')
            ->get();

        // Get attendances for the selected date
        $attendances = Attendance::with('user')
            ->whereDate('created_at', $selectedDate)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->keyBy('user_id');

        // Build report data
        $reportData = $users->map(function ($user) use ($attendances, $selectedDate) {
            $attendance = $attendances->get($user->id);
            
            // Get Indonesian day name (lowercase)
            $dayName = strtolower($selectedDate->locale('id')->dayName);
            
            $workSchedule = $user->workSchedules
                ->where('day', $dayName)
                ->where('is_active', true)
                ->first();

            return [
                'user' => $user,
                'work_schedule' => $workSchedule,
                'attendance' => $attendance,
                'status' => $this->determineStatus($attendance, $workSchedule),
            ];
        });

        // Calculate statistics
        $stats = [
            'total_employees' => $users->count(),
            'checked_in' => $attendances->count(),
            'checked_out' => $attendances->filter(fn($a) => $a->check_out_at !== null)->count(),
        ];

        $pdf = Pdf::loadView('admin.reports.daily-pdf', compact(
            'reportData',
            'stats',
            'selectedDate',
            'activeYear'
        ));

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("rekap-harian-{$selectedDate->format('Y-m-d')}.pdf");
    }

    /**
     * Display the monthly attendance report.
     */
    public function monthly(Request $request): View
    {
        // Default to current month range
        $startDate = $request->input('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->endOfMonth()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Get active academic year
        $activeYear = AcademicYear::getActive();
        
        // Get all users (employees)
        $users = User::with(['role', 'office'])
            ->whereHas('role', fn($q) => $q->where('is_admin', false))
            ->orderBy('name')
            ->get();

        // Get attendances for the date range
        $attendances = Attendance::with('user')
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->groupBy('user_id');

        // Count work days in range (excluding weekends)
        $workDays = $this->countWorkDaysInRange($start, $end);

        // Build summary
        $reportData = $users->map(function ($user) use ($attendances, $workDays) {
            $userAttendances = $attendances->get($user->id, collect());
            
            return [
                'user' => $user,
                'total_present' => $userAttendances->count(),
                'total_late' => $userAttendances->filter(fn($a) => $a->status->value === 'late')->count(),
                'total_on_time' => $userAttendances->filter(fn($a) => $a->status->value === 'present')->count(),
                'total_alpha' => $workDays - $userAttendances->count(),
                'work_days' => $workDays,
                'attendance_rate' => $workDays > 0 ? round(($userAttendances->count() / $workDays) * 100, 1) : 0,
            ];
        });

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.reports.monthly', compact(
            'reportData',
            'startDate',
            'endDate',
            'activeYear',
            'academicYears',
            'workDays'
        ));
    }

    /**
     * Export monthly attendance report as PDF.
     */
    public function exportMonthlyPdf(Request $request): Response
    {
        $startDate = $request->input('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->endOfMonth()->format('Y-m-d'));
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Get active academic year
        $activeYear = AcademicYear::getActive();
        
        // Get all users (employees)
        $users = User::with(['role', 'office'])
            ->whereHas('role', fn($q) => $q->where('is_admin', false))
            ->orderBy('name')
            ->get();

        // Get attendances for the date range
        $attendances = Attendance::with('user')
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->groupBy('user_id');

        // Count work days in range
        $workDays = $this->countWorkDaysInRange($start, $end);

        // Build summary
        $reportData = $users->map(function ($user) use ($attendances, $workDays) {
            $userAttendances = $attendances->get($user->id, collect());
            
            return [
                'user' => $user,
                'total_present' => $userAttendances->count(),
                'total_late' => $userAttendances->filter(fn($a) => $a->status->value === 'late')->count(),
                'total_on_time' => $userAttendances->filter(fn($a) => $a->status->value === 'present')->count(),
                'total_alpha' => $workDays - $userAttendances->count(),
                'work_days' => $workDays,
                'attendance_rate' => $workDays > 0 ? round(($userAttendances->count() / $workDays) * 100, 1) : 0,
            ];
        });

        $pdf = Pdf::loadView('admin.reports.monthly-pdf', compact(
            'reportData',
            'startDate',
            'endDate',
            'activeYear',
            'workDays'
        ));

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("rekap-bulanan-{$start->format('Y-m-d')}-{$end->format('Y-m-d')}.pdf");
    }

    /**
     * Determine attendance status for a user.
     */
    private function determineStatus(?Attendance $attendance, $workSchedule): string
    {
        if (!$workSchedule) {
            return 'no_schedule';
        }

        if (!$attendance) {
            return 'absent';
        }

        return $attendance->status->value;
    }

    /**
     * Count work days in a month (excluding weekends).
     */
    private function countWorkDays(Carbon $month): int
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        return $this->countWorkDaysInRange($start, $end);
    }

    /**
     * Count work days in a date range (excluding weekends).
     */
    private function countWorkDaysInRange(Carbon $start, Carbon $end): int
    {
        $startCopy = $start->copy();
        $endCopy = $end->copy();
        $workDays = 0;

        while ($startCopy <= $endCopy) {
            if (!$startCopy->isWeekend()) {
                $workDays++;
            }
            $startCopy->addDay();
        }

        return $workDays;
    }
}
