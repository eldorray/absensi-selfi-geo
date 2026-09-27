<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Builds the admin dashboard figures for one day.
 *
 * Every non-admin employee lands in exactly one bucket: on time, late, on
 * approved leave, not yet checked in (scheduled today), or off (no schedule
 * and no attendance). "Off" is left out of the day's total so a teacher
 * without lessons today is never counted as missing.
 */
final class AdminDashboardService
{
    public const ON_TIME = 'on_time';

    public const LATE = 'late';

    public const LEAVE = 'leave';

    public const NOT_YET = 'not_yet';

    public const OFF = 'off';

    /**
     * View data for admin.dashboard: totals per bucket, per-office breakdown,
     * the not-yet list, the pending leave queue size, and recent check-ins.
     *
     * @return array<string, mixed>
     */
    public function build(CarbonInterface $day): array
    {
        $activeYearId = AcademicYear::getActive()?->id;
        $localized = $day->copy();
        $localized->locale('id');
        $dayName = strtolower($localized->dayName);

        $employees = User::query()
            ->with([
                'office',
                'workSchedules' => fn ($q) => $q
                    ->where('academic_year_id', $activeYearId)
                    ->where('day', $dayName)
                    ->where('is_active', true),
            ])
            ->whereHas('role', fn ($q) => $q->where('is_admin', false))
            ->orderBy('name')
            ->get();

        $attendances = Attendance::query()
            ->whereDate('created_at', $day)
            ->get()
            ->keyBy('user_id');

        $onLeave = Leave::query()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $day)
            ->whereDate('end_date', '>=', $day)
            ->pluck('user_id')
            ->flip();

        $bucketed = $employees->map(fn (User $user): array => [
            'user' => $user,
            'bucket' => $this->bucketFor($user, $attendances->get($user->id), $onLeave->has($user->id), $activeYearId !== null),
        ]);

        $totals = $this->count($bucketed);
        $expected = $totals[self::ON_TIME] + $totals[self::LATE] + $totals[self::LEAVE] + $totals[self::NOT_YET];
        $present = $totals[self::ON_TIME] + $totals[self::LATE];

        $offices = $bucketed
            ->groupBy(fn (array $row): string => $row['user']->office->name ?? 'Tanpa kantor')
            ->map(function (Collection $rows, string $name): array {
                $counts = $this->count($rows);

                return [
                    'name' => $name,
                    'counts' => $counts,
                    'present' => $counts[self::ON_TIME] + $counts[self::LATE],
                    'expected' => $counts[self::ON_TIME] + $counts[self::LATE] + $counts[self::LEAVE] + $counts[self::NOT_YET],
                ];
            })
            ->filter(fn (array $office): bool => $office['expected'] > 0)
            ->sortKeys()
            ->values();

        $pending = Leave::pending()->get(['type']);
        $typeLabels = ['izin' => 'izin', 'sakit' => 'sakit', 'cuti' => 'cuti'];
        $pendingLeaveSummary = $pending->countBy('type')
            ->map(fn (int $count, string $type): string => $count.' '.($typeLabels[$type] ?? $type))
            ->implode(', ');

        return [
            'totals' => $totals,
            'expected' => $expected,
            'present' => $present,
            'presentPct' => $expected > 0 ? (int) round($present / $expected * 100) : 0,
            'offices' => $offices,
            'notYet' => $bucketed->where('bucket', self::NOT_YET)->map(fn (array $row): User => $row['user'])->values(),
            'pendingLeaves' => $pending->count(),
            'pendingLeaveSummary' => $pendingLeaveSummary,
            'manualPhotos' => $attendances
                ->filter(fn (Attendance $a): bool => $a->liveness_verified === false || $a->check_out_liveness_verified === false)
                ->count(),
            'recentAttendances' => Attendance::with(['user', 'user.office'])->latest()->take(8)->get(),
        ];
    }

    private function bucketFor(User $user, ?Attendance $attendance, bool $onLeave, bool $hasActiveYear): string
    {
        if ($attendance !== null) {
            return $attendance->status === AttendanceStatus::Late ? self::LATE : self::ON_TIME;
        }

        if ($onLeave) {
            return self::LEAVE;
        }

        return $hasActiveYear && $user->workSchedules->isNotEmpty() ? self::NOT_YET : self::OFF;
    }

    /**
     * @param  Collection<int, array{user: User, bucket: string}>  $rows
     * @return array<string, int>
     */
    private function count(Collection $rows): array
    {
        $counts = $rows->countBy('bucket');

        return collect([self::ON_TIME, self::LATE, self::LEAVE, self::NOT_YET, self::OFF])
            ->mapWithKeys(fn (string $bucket): array => [$bucket => (int) $counts->get($bucket, 0)])
            ->all();
    }
}
