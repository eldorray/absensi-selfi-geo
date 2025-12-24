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
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Controller for generating attendance reports.
 * 
 * Handles both daily and monthly attendance reports with PDF export.
 */
class ReportController extends Controller
{
    /**
     * Display the daily attendance report.
     */
    public function daily(Request $request): View
    {
        $data = $this->getDailyReportData($request);
        $data['academicYears'] = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.reports.daily', $data);
    }

    /**
     * Export daily attendance report as PDF.
     */
    public function exportDailyPdf(Request $request): Response
    {
        $data = $this->getDailyReportData($request);

        $pdf = Pdf::loadView('admin.reports.daily-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("rekap-harian-{$data['selectedDate']->format('Y-m-d')}.pdf");
    }

    /**
     * Display the monthly attendance report.
     */
    public function monthly(Request $request): View
    {
        $data = $this->getMonthlyReportData($request);
        $data['academicYears'] = AcademicYear::orderByDesc('start_date')->get();

        return view('admin.reports.monthly', $data);
    }

    /**
     * Export monthly attendance report as PDF.
     */
    public function exportMonthlyPdf(Request $request): Response
    {
        $data = $this->getMonthlyReportData($request);

        $pdf = Pdf::loadView('admin.reports.monthly-pdf', $data);
        $pdf->setPaper('a4', 'landscape');

        $start = Carbon::parse($data['startDate']);
        $end = Carbon::parse($data['endDate']);

        return $pdf->download("rekap-bulanan-{$start->format('Y-m-d')}-{$end->format('Y-m-d')}.pdf");
    }

    /**
     * Get daily report data.
     * 
     * Shared logic between daily() and exportDailyPdf().
     *
     * @return array{reportData: Collection, stats: array, selectedDate: Carbon, activeYear: ?AcademicYear}
     */
    private function getDailyReportData(Request $request): array
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $activeYear = AcademicYear::getActive();
        $users = $this->getEmployees(withSchedules: true);
        $attendances = $this->getDailyAttendances($selectedDate, $activeYear);

        $reportData = $users->map(fn($user) => $this->buildDailyUserReport($user, $attendances, $selectedDate));

        $stats = [
            'total_employees' => $users->count(),
            'checked_in' => $attendances->count(),
            'checked_out' => $attendances->filter(fn($a) => $a->check_out_at !== null)->count(),
        ];

        return compact('reportData', 'stats', 'selectedDate', 'activeYear');
    }

    /**
     * Get monthly report data.
     * 
     * Shared logic between monthly() and exportMonthlyPdf().
     *
     * @return array{reportData: Collection, startDate: string, endDate: string, activeYear: ?AcademicYear, workDays: int}
     */
    private function getMonthlyReportData(Request $request): array
    {
        $startDate = $request->input('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->endOfMonth()->format('Y-m-d'));

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $activeYear = AcademicYear::getActive();
        $users = $this->getEmployees(withSchedules: false);
        $attendances = $this->getRangeAttendances($start, $end, $activeYear);
        $workDays = $this->countWorkDaysInRange($start, $end);

        $reportData = $users->map(fn($user) => $this->buildMonthlyUserReport($user, $attendances, $workDays));

        return compact('reportData', 'startDate', 'endDate', 'activeYear', 'workDays');
    }

    /**
     * Get all employees (non-admin users).
     */
    private function getEmployees(bool $withSchedules = false): Collection
    {
        $relations = ['role', 'office'];
        if ($withSchedules) {
            $relations[] = 'workSchedules';
        }

        return User::with($relations)
            ->whereHas('role', fn($q) => $q->where('is_admin', false))
            ->orderBy('name')
            ->get();
    }

    /**
     * Get attendances for a specific date.
     */
    private function getDailyAttendances(Carbon $date, ?AcademicYear $activeYear): Collection
    {
        return Attendance::with('user')
            ->whereDate('created_at', $date)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->keyBy('user_id');
    }

    /**
     * Get attendances for a date range.
     */
    private function getRangeAttendances(Carbon $start, Carbon $end, ?AcademicYear $activeYear): Collection
    {
        return Attendance::with('user')
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->groupBy('user_id');
    }

    /**
     * Build daily report data for a single user.
     */
    private function buildDailyUserReport(User $user, Collection $attendances, Carbon $date): array
    {
        $attendance = $attendances->get($user->id);
        $dayName = strtolower($date->locale('id')->dayName);

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
    }

    /**
     * Build monthly report data for a single user.
     */
    private function buildMonthlyUserReport(User $user, Collection $attendances, int $workDays): array
    {
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
