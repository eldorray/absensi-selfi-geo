<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * WorkSchedule Model
 *
 * Per-user daily work schedule with check-in/out times.
 */
class WorkSchedule extends Model
{
    use HasFactory;

    /**
     * Available days.
     */
    public const DAYS = [
        'senin' => 'Senin',
        'selasa' => 'Selasa',
        'rabu' => 'Rabu',
        'kamis' => 'Kamis',
        'jumat' => 'Jumat',
        'sabtu' => 'Sabtu',
        'minggu' => 'Minggu',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'day',
        'check_in_time',
        'check_out_time',
        'is_active',
    ];

    /**
     * Stamp the active academic year on new schedules that don't specify one.
     */
    protected static function booted(): void
    {
        static::creating(function (self $schedule): void {
            if ($schedule->academic_year_id === null) {
                $schedule->setAttribute('academic_year_id', AcademicYear::getActive()?->id);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Default check-out time used when a day has no active schedule.
     */
    public const DEFAULT_CHECK_OUT_TIME = '16:00:00';

    /**
     * The earliest moment check-out is allowed for today: the schedule's
     * check-out time minus the "before check-out" window (in minutes).
     */
    public static function checkoutOpensAt(?self $schedule, int $beforeMinutes): Carbon
    {
        $checkOutTime = $schedule ? $schedule->check_out_time : self::DEFAULT_CHECK_OUT_TIME;

        return Carbon::parse($checkOutTime)->subMinutes($beforeMinutes);
    }

    /**
     * The lowercase Indonesian name of today's day (e.g. "senin").
     */
    public static function todayDayName(): string
    {
        $now = now();
        $now->locale('id');

        return strtolower($now->dayName);
    }

    /**
     * A user's active schedule for today, scoped to the active academic year.
     *
     * Returns null when there is no active year or no matching schedule; the
     * caller then falls back to the default times above.
     */
    public static function todayFor(int $userId): ?self
    {
        $activeYearId = AcademicYear::getActive()?->id;

        if ($activeYearId === null) {
            return null;
        }

        return self::query()
            ->where('user_id', $userId)
            ->where('academic_year_id', $activeYearId)
            ->where('day', self::todayDayName())
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the user that owns this schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the academic year this schedule belongs to.
     *
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the day label.
     */
    public function getDayLabelAttribute(): string
    {
        return self::DAYS[$this->day] ?? $this->day;
    }
}
