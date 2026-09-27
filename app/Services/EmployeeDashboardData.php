<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * The figures behind an employee's "beranda", shared by the Blade dashboard
 * and the mobile API so both report the same numbers.
 */
final readonly class EmployeeDashboardData
{
    /**
     * @param  Collection<int, Announcement>  $announcements
     */
    public function __construct(
        public ?Attendance $todayAttendance,
        public ?WorkSchedule $todaySchedule,
        public Carbon $checkoutOpensAt,
        public bool $checkoutTimeReached,
        public int $monthlyPresent,
        public int $monthlyLate,
        public Collection $announcements,
        public int $monthlyLeaveDays = 0,
        public int $monthlyWorkDays = 0,
        public int $pendingLeaves = 0,
        public int $unreadNotifications = 0,
    ) {}

    /**
     * Days attended this month. "Hadir" counts on-time and late alike, so the
     * total matches the present figure.
     */
    public function monthlyTotal(): int
    {
        return $this->monthlyPresent;
    }

    public function monthlyOnTime(): int
    {
        return max(0, $this->monthlyPresent - $this->monthlyLate);
    }

    /**
     * Work days this month that have either an attendance or an approved leave.
     */
    public function monthlyRecorded(): int
    {
        return min($this->monthlyPresent + $this->monthlyLeaveDays, $this->monthlyWorkDays);
    }
}
