<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Attendance Status Enum
 *
 * Represents the status of an attendance record.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';

    /**
     * Get the display label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Present => 'Hadir',
            self::Late => 'Terlambat',
        };
    }

    /**
     * Get the CSS class for styling the status badge.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Present => 'bg-green-100 text-green-800',
            self::Late => 'bg-yellow-100 text-yellow-800',
        };
    }
}
