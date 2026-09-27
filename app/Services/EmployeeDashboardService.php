<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;

/**
 * Builds the employee "beranda" figures.
 *
 * Extracted from Employee\DashboardController so the Blade dashboard and the
 * mobile API read from one implementation instead of two.
 */
final class EmployeeDashboardService
{
    public function for(User $user): EmployeeDashboardData
    {
        $todayAttendance = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        $todaySchedule = WorkSchedule::todayFor((int) $user->id);

        $checkoutOpensAt = WorkSchedule::checkoutOpensAt(
            $todaySchedule,
            WorkSetting::current()->before_check_out,
        );

        $monthStart = now()->startOfMonth();

        // "Hadir" covers on-time and late alike; late is also counted on its own.
        $monthlyPresent = Attendance::query()
            ->where('user_id', $user->id)
            ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
            ->whereBetween('created_at', [$monthStart, now()])
            ->count();

        $monthlyLate = Attendance::query()
            ->where('user_id', $user->id)
            ->where('status', AttendanceStatus::Late)
            ->whereBetween('created_at', [$monthStart, now()])
            ->count();

        return new EmployeeDashboardData(
            todayAttendance: $todayAttendance,
            todaySchedule: $todaySchedule,
            checkoutOpensAt: $checkoutOpensAt,
            checkoutTimeReached: now()->gte($checkoutOpensAt),
            monthlyPresent: $monthlyPresent,
            monthlyLate: $monthlyLate,
            announcements: Announcement::activeOrdered()
                ->visibleToOffice($user->office_id)
                ->get(),
            monthlyLeaveDays: $this->approvedLeaveDaysThisMonth($user),
            monthlyWorkDays: $this->scheduledDaysThisMonth($user),
            pendingLeaves: Leave::query()->where('user_id', $user->id)->pending()->count(),
            unreadNotifications: $user->unreadNotifications()->where('type', 'like', '%StudentReferral%')->count(),
        );
    }

    /**
     * Distinct dates this month, up to today, covered by the user's approved leaves.
     */
    private function approvedLeaveDaysThisMonth(User $user): int
    {
        $from = now()->startOfMonth();
        $to = now()->startOfDay();
        $dates = [];

        Leave::query()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->get(['start_date', 'end_date'])
            ->each(function (Leave $leave) use ($from, $to, &$dates): void {
                $day = $leave->start_date->copy()->max($from)->copy()->startOfDay();
                $last = $leave->end_date->copy()->min($to)->copy();

                for (; $day->lte($last); $day->addDay()) {
                    $dates[$day->toDateString()] = true;
                }
            });

        return count($dates);
    }

    /**
     * Dates this month, up to today, whose weekday has an active schedule in the
     * active academic year.
     */
    private function scheduledDaysThisMonth(User $user): int
    {
        $yearId = AcademicYear::getActive()?->id;

        if ($yearId === null) {
            return 0;
        }

        $days = WorkSchedule::query()
            ->where('user_id', $user->id)
            ->where('academic_year_id', $yearId)
            ->where('is_active', true)
            ->pluck('day')
            ->all();

        if ($days === []) {
            return 0;
        }

        $count = 0;

        for ($day = now()->startOfMonth(); $day->lte(now()->startOfDay()); $day->addDay()) {
            $day->locale('id');

            if (in_array(strtolower($day->dayName), $days, true)) {
                $count++;
            }
        }

        return $count;
    }
}
