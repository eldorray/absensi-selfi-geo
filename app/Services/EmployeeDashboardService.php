<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Announcement;
use App\Models\Attendance;
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
        );
    }
}
