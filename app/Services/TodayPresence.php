<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\WorkSetting;
use Carbon\Carbon;

/**
 * State of the "Presensi hari ini" hero card on the teacher beranda.
 */
final readonly class TodayPresence
{
    /**
     * @param  array{label: string, href: ?string, icon: string, disabled: bool}|null  $action
     */
    public function __construct(
        public string $status,
        public bool $late,
        public ?string $checkIn,
        public ?string $checkOut,
        public ?string $scheduleStart,
        public ?string $scheduleEnd,
        public int $progress,
        public string $progressText,
        public string $locationText,
        public ?array $action,
    ) {}

    public static function for(EmployeeDashboardData $data, WorkSetting $settings, Carbon $now): self
    {
        $attendance = $data->todayAttendance;
        $schedule = $data->todaySchedule;
        $late = $attendance?->status === AttendanceStatus::Late;

        if ($schedule === null) {
            return new self(
                status: $attendance ? ($late ? 'Terlambat' : 'Tepat waktu') : 'Libur',
                late: $late,
                checkIn: $attendance?->created_at?->format('H.i'),
                checkOut: $attendance?->check_out_at?->format('H.i'),
                scheduleStart: null,
                scheduleEnd: null,
                progress: 0,
                progressText: 'Tidak ada jadwal kerja hari ini',
                locationText: self::location($attendance),
                action: null,
            );
        }

        $start = Carbon::parse($schedule->check_in_time)->setDateFrom($now);
        $end = Carbon::parse($schedule->check_out_time)->setDateFrom($now);
        $span = max(1, $end->getTimestamp() - $start->getTimestamp());
        $progress = (int) round(min(100, max(0, ($now->getTimestamp() - $start->getTimestamp()) / $span * 100)));

        if ($attendance === null) {
            $opens = $start->copy()->subMinutes((int) $settings->before_check_in);
            $lateAfter = $start->copy()->addMinutes((int) $settings->after_check_in);
            $closes = $start->copy()->addMinutes((int) $settings->late_limit);

            [$status, $text] = match (true) {
                $now->lt($opens) => ['Belum absen', 'Absen masuk dibuka '.$opens->format('H.i')],
                $now->lte($lateAfter) => ['Belum absen', 'Tepat waktu s.d. '.$lateAfter->format('H.i')],
                $now->lte($closes) => ['Belum absen', 'Terlambat · tutup '.$closes->format('H.i')],
                default => ['Absen ditutup', 'Absen masuk ditutup '.$closes->format('H.i')],
            };

            return new self(
                status: $status,
                late: false,
                checkIn: null,
                checkOut: null,
                scheduleStart: $start->format('H.i'),
                scheduleEnd: $end->format('H.i'),
                progress: $progress,
                progressText: $text,
                locationText: 'Lokasi dicek saat absen',
                action: $now->gt($closes)
                    ? null
                    : ['label' => 'Absen Masuk', 'href' => route('attendance.selfie'), 'icon' => 'login', 'disabled' => false],
            );
        }

        $status = $late ? 'Terlambat' : 'Tepat waktu';
        $checkIn = $attendance->created_at?->format('H.i');

        if ($attendance->check_out_at !== null) {
            return new self(
                status: $status,
                late: $late,
                checkIn: $checkIn,
                checkOut: $attendance->check_out_at->format('H.i'),
                scheduleStart: $start->format('H.i'),
                scheduleEnd: $end->format('H.i'),
                progress: 100,
                progressText: 'Selesai hari ini',
                locationText: self::location($attendance),
                action: ['label' => 'Lihat riwayat', 'href' => route('attendance.index'), 'icon' => 'calendar', 'disabled' => false],
            );
        }

        if ($data->checkoutTimeReached) {
            return new self(
                status: $status,
                late: $late,
                checkIn: $checkIn,
                checkOut: null,
                scheduleStart: $start->format('H.i'),
                scheduleEnd: $end->format('H.i'),
                progress: $progress,
                progressText: 'Absen pulang sudah dibuka',
                locationText: self::location($attendance),
                action: ['label' => 'Absen Pulang', 'href' => route('attendance.checkout'), 'icon' => 'logout', 'disabled' => false],
            );
        }

        $minutes = (int) ceil(max(0, $data->checkoutOpensAt->getTimestamp() - $now->getTimestamp()) / 60);

        return new self(
            status: $status,
            late: $late,
            checkIn: $checkIn,
            checkOut: null,
            scheduleStart: $start->format('H.i'),
            scheduleEnd: $end->format('H.i'),
            progress: $progress,
            progressText: 'Pulang dalam '.self::duration($minutes),
            locationText: self::location($attendance),
            action: ['label' => 'Pulang dibuka '.$data->checkoutOpensAt->format('H.i'), 'href' => null, 'icon' => 'clock', 'disabled' => true],
        );
    }

    private static function duration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return $hours > 0 ? "{$hours} j {$rest} m" : "{$rest} m";
    }

    private static function location(?Attendance $attendance): string
    {
        if ($attendance === null) {
            return 'Lokasi dicek saat absen';
        }

        $text = 'Dalam area sekolah · '.number_format((float) $attendance->distance_meters, 0, ',', '.').' m dari titik absen';

        return $attendance->liveness_verified === false ? $text.' · foto manual, menunggu pemeriksaan' : $text;
    }
}
