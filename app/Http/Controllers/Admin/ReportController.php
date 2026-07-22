<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\User;
use App\Models\WorkSetting;
use App\Services\ImageService;
use App\Support\FineCalculator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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
        $data['offices'] = Office::orderBy('name')->get();

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
     * Reset (delete) a single attendance record and its selfie images, so the
     * employee can record their attendance for that day again.
     */
    public function resetAttendance(Attendance $attendance, ImageService $imageService): RedirectResponse
    {
        $user = $attendance->user;
        $name = $user instanceof User ? $user->name : 'guru';

        $imageService->deleteImage($attendance->image_path);
        $imageService->deleteImage($attendance->check_out_image_path);

        $attendance->delete();

        return back()->with('success', "Absensi {$name} berhasil direset.");
    }

    /**
     * Display the monthly attendance report.
     */
    public function monthly(Request $request): View
    {
        $data = $this->getMonthlyReportData($request);
        $data['academicYears'] = AcademicYear::orderByDesc('start_date')->get();
        $data['offices'] = Office::orderBy('name')->get();

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
        $settings = WorkSetting::current();
        $selectedOffice = $this->resolveOffice($request);
        $users = $this->getEmployees(withSchedules: true, officeId: $selectedOffice?->id, academicYearId: $activeYear?->id);
        $attendances = $this->getDailyAttendances($selectedDate, $activeYear);

        // Scope attendances to the filtered employees so the stat cards match
        // the table rows. Eloquent Collection::only() filters by primary key,
        // not the user_id array key, so filter explicitly.
        $userIds = $users->pluck('id')->all();
        $attendances = $attendances->filter(fn ($a) => in_array($a->user_id, $userIds, true));

        $reportData = $users->map(fn ($user) => $this->buildDailyUserReport($user, $attendances, $selectedDate, $settings));

        $officeId = $selectedOffice?->id;

        $stats = [
            'total_employees' => $users->count(),
            'checked_in' => $attendances->count(),
            'checked_out' => $attendances->filter(fn ($a) => $a->check_out_at !== null)->count(),
            'total_fine' => (int) $reportData->sum('fine'),
        ];

        return compact('reportData', 'stats', 'selectedDate', 'activeYear', 'settings', 'selectedOffice', 'officeId');
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
        $settings = WorkSetting::current();
        $selectedOffice = $this->resolveOffice($request);
        $users = $this->getEmployees(withSchedules: true, officeId: $selectedOffice?->id, academicYearId: $activeYear?->id);
        $attendances = $this->getRangeAttendances($start, $end, $activeYear);
        $workDays = $this->countWorkDaysInRange($start, $end);

        $reportData = $users->map(fn ($user) => $this->buildMonthlyUserReport($user, $attendances, $workDays, $settings));

        $totalFine = (int) $reportData->sum('total_fine');
        $officeId = $selectedOffice?->id;

        return compact('reportData', 'startDate', 'endDate', 'activeYear', 'workDays', 'settings', 'totalFine', 'selectedOffice', 'officeId');
    }

    /**
     * Resolve the office filter from the request, or null when absent/invalid.
     */
    private function resolveOffice(Request $request): ?Office
    {
        $officeId = $request->input('office_id');

        return $officeId ? Office::find((int) $officeId) : null;
    }

    /**
     * Get all employees (non-admin users), optionally scoped to one office.
     * When schedules are loaded, they are scoped to the given academic year so
     * fines/statuses use the schedule of the year being reported on.
     */
    private function getEmployees(bool $withSchedules = false, ?int $officeId = null, ?int $academicYearId = null): Collection
    {
        $relations = ['role', 'office'];
        if ($withSchedules) {
            $relations['workSchedules'] = fn ($q) => $q->where('academic_year_id', $academicYearId);
        }

        return User::with($relations)
            ->whereHas('role', fn ($q) => $q->where('is_admin', false))
            ->when($officeId, fn ($q) => $q->where('office_id', $officeId))
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
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
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
            ->when($activeYear, fn ($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->groupBy('user_id');
    }

    /**
     * Build daily report data for a single user.
     */
    private function buildDailyUserReport(User $user, Collection $attendances, Carbon $date, WorkSetting $settings): array
    {
        $attendance = $attendances->get($user->id);
        $dayName = strtolower($date->locale('id')->dayName);

        $workSchedule = $user->workSchedules
            ->where('day', $dayName)
            ->where('is_active', true)
            ->first();

        $lateMinutes = FineCalculator::lateMinutes($attendance, $workSchedule, $settings);

        return [
            'user' => $user,
            'work_schedule' => $workSchedule,
            'attendance' => $attendance,
            'status' => $this->determineStatus($attendance, $workSchedule),
            'late_minutes' => $lateMinutes,
            'fine' => FineCalculator::amountForMinutes($lateMinutes, $settings),
        ];
    }

    /**
     * Build monthly report data for a single user.
     */
    private function buildMonthlyUserReport(User $user, Collection $attendances, int $workDays, WorkSetting $settings): array
    {
        $userAttendances = $attendances->get($user->id, collect());

        $totalFine = $userAttendances->sum(function ($attendance) use ($user, $settings) {
            $dayName = strtolower($attendance->created_at->locale('id')->dayName);
            $schedule = $user->workSchedules
                ->where('day', $dayName)
                ->where('is_active', true)
                ->first();

            return FineCalculator::fine($attendance, $schedule, $settings);
        });

        return [
            'user' => $user,
            'total_present' => $userAttendances->count(),
            'total_late' => $userAttendances->filter(fn ($a) => $a->status->value === 'late')->count(),
            'total_on_time' => $userAttendances->filter(fn ($a) => $a->status->value === 'present')->count(),
            'total_alpha' => $workDays - $userAttendances->count(),
            'work_days' => $workDays,
            'attendance_rate' => $workDays > 0 ? round(($userAttendances->count() / $workDays) * 100, 1) : 0,
            'total_fine' => (int) $totalFine,
        ];
    }

    /**
     * Determine attendance status for a user.
     */
    private function determineStatus(?Attendance $attendance, $workSchedule): string
    {
        if (! $workSchedule) {
            return 'no_schedule';
        }

        if (! $attendance) {
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
            if (! $startCopy->isWeekend()) {
                $workDays++;
            }
            $startCopy->addDay();
        }

        return $workDays;
    }
}
