<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use App\Traits\HasHaversineCalculation;
use Carbon\Carbon;

/**
 * The rules behind checking in and out.
 *
 * Extracted from Employee\AttendanceController so the Blade app and the mobile
 * API enforce one set of rules. Only the rules live here; each caller keeps its
 * own way of receiving a photo (base64 from the browser, an upload from iOS).
 */
final class AttendanceService
{
    use HasHaversineCalculation;

    /**
     * Check-in time assumed on a day with no active schedule.
     */
    public const DEFAULT_CHECK_IN_TIME = '07:00:00';

    /**
     * Today's attendance row for a user, if they have checked in.
     */
    public function todayFor(User $user): ?Attendance
    {
        return Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();
    }

    public function hasCheckedInToday(User $user): bool
    {
        return Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Why today is not a working day, or null when it is.
     */
    public function dayOffError(User $user): ?string
    {
        $schedule = WorkSchedule::todayFor((int) $user->id);

        if (! $schedule && WorkSchedule::todayDayName() === 'minggu') {
            return 'Hari ini (Minggu) adalah hari libur.';
        }

        return null;
    }

    /**
     * Why check-in is closed at a given moment, or null when it is open.
     *
     * The moment defaults to now, which keeps the Blade controller — and the
     * PWA behind it — behaving exactly as before. The mobile API passes the
     * device capture time so a queued check-in is judged when it happened.
     */
    public function checkInWindowError(User $user, ?Carbon $at = null): ?string
    {
        $settings = WorkSetting::current();
        $scheduled = $this->scheduledCheckIn($user);

        $earliest = $scheduled->copy()->subMinutes($settings->before_check_in);
        $latest = $scheduled->copy()->addMinutes($settings->late_limit);
        $moment = $at ?? now();

        if ($moment->lt($earliest)) {
            return 'Anda belum dapat absen. Waktu absen dimulai pukul '.$earliest->format('H:i').'.';
        }

        if ($moment->gt($latest)) {
            return 'Waktu absen sudah berakhir. Batas absen adalah pukul '.$latest->format('H:i').'.';
        }

        return null;
    }

    /**
     * Why check-out is still closed at a given moment, or null when it opened.
     */
    public function checkOutWindowError(User $user, ?Carbon $at = null): ?string
    {
        $opensAt = WorkSchedule::checkoutOpensAt(
            WorkSchedule::todayFor((int) $user->id),
            WorkSetting::current()->before_check_out,
        );

        if (($at ?? now())->lt($opensAt)) {
            return 'Belum waktunya absen pulang. Absen pulang dibuka pukul '.$opensAt->format('H:i').'.';
        }

        return null;
    }

    /**
     * On time or late at a given moment, judged against the schedule plus the
     * grace period.
     */
    public function statusAt(User $user, ?Carbon $at = null): AttendanceStatus
    {
        $lateAfter = $this->scheduledCheckIn($user)
            ->copy()
            ->addMinutes(WorkSetting::current()->after_check_in);

        return ($at ?? now())->gt($lateAfter) ? AttendanceStatus::Late : AttendanceStatus::Present;
    }

    /**
     * On time or late right now. Kept so existing callers stay untouched.
     */
    public function statusNow(User $user): AttendanceStatus
    {
        return $this->statusAt($user);
    }

    /**
     * The office attendance is measured against.
     *
     * A user's assigned office always wins, so a tampered office id in the
     * request cannot move the geofence somewhere convenient. Returns null when
     * the user has no office and none was supplied.
     */
    public function officeFor(User $user, ?int $requestedOfficeId = null): ?Office
    {
        $officeId = $user->office_id ?? $requestedOfficeId;

        return $officeId === null ? null : Office::find($officeId);
    }

    /**
     * Metres between a coordinate and the centre of an office.
     */
    public function distanceFrom(Office $office, float $latitude, float $longitude): float
    {
        return $this->calculateHaversineDistance(
            $latitude,
            $longitude,
            (float) $office->latitude,
            (float) $office->longitude,
        );
    }

    /**
     * The message shown when a coordinate falls outside the office radius, or
     * null when it is close enough.
     */
    public function outOfRangeError(Office $office, float $distance): ?string
    {
        if ($distance <= $office->radius_meters) {
            return null;
        }

        return sprintf(
            'Anda berada %.0f meter dari kantor. Jarak maksimal yang diizinkan adalah %d meter.',
            $distance,
            $office->radius_meters,
        );
    }

    /**
     * Today's scheduled check-in time, falling back to the default when the day
     * has no active schedule.
     */
    private function scheduledCheckIn(User $user): Carbon
    {
        $schedule = WorkSchedule::todayFor((int) $user->id);
        $time = $schedule !== null ? $schedule->check_in_time : self::DEFAULT_CHECK_IN_TIME;

        return Carbon::parse($time);
    }
}
