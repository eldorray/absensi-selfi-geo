<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;

/**
 * Calculates late-arrival fines (denda) for attendance records.
 *
 * Late minutes are measured from the tolerance threshold
 * (scheduled check-in + after_check_in), and a fine only applies to
 * records with the "Late" status. Amounts and the tier boundary are
 * read from WorkSetting so admins can tune them.
 */
class FineCalculator
{
    /**
     * Minutes late past the tolerance threshold. 0 when not late.
     */
    public static function lateMinutes(?Attendance $attendance, ?WorkSchedule $schedule, WorkSetting $settings): int
    {
        if (! $attendance || $attendance->status !== AttendanceStatus::Late) {
            return 0;
        }

        $checkInTime = $schedule->check_in_time ?? '07:00:00';
        $threshold = $attendance->created_at->copy()
            ->setTimeFromTimeString($checkInTime)
            ->addMinutes($settings->after_check_in);

        $minutes = (int) floor($threshold->diffInMinutes($attendance->created_at, false));

        // A Late record is always at least 1 minute past tolerance.
        return max(1, $minutes);
    }

    /**
     * Fine amount (rupiah) for a given number of late minutes.
     *
     * Pure tier function — exposed for unit testing.
     */
    public static function amountForMinutes(int $lateMinutes, WorkSetting $settings): int
    {
        if ($lateMinutes <= 0) {
            return 0;
        }

        return $lateMinutes <= $settings->fine_tier1_max_minutes
            ? $settings->fine_tier1_amount
            : $settings->fine_tier2_amount;
    }

    /**
     * Fine amount (rupiah) for one attendance record.
     */
    public static function fine(?Attendance $attendance, ?WorkSchedule $schedule, WorkSetting $settings): int
    {
        return self::amountForMinutes(self::lateMinutes($attendance, $schedule, $settings), $settings);
    }
}
