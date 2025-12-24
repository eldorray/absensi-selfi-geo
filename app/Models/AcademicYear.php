<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AcademicYear Model - Represents an academic year period for attendance tracking.
 */
class AcademicYear extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get all attendances for this academic year.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scope to get only the active academic year.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the currently active academic year.
     */
    public static function getActive(): ?self
    {
        return static::active()->first();
    }

    /**
     * Activate this academic year and deactivate all others.
     * Optionally reset work schedules for the new year.
     */
    public function activate(bool $resetSchedules = true): void
    {
        // Deactivate all other academic years
        static::where('id', '!=', $this->id)->update(['is_active' => false]);
        
        // Activate this one
        $this->update(['is_active' => true]);
        
        // Reset work schedules if requested
        if ($resetSchedules) {
            WorkSchedule::query()->update(['is_active' => false]);
        }
    }

    /**
     * Check if a date falls within this academic year.
     */
    public function containsDate($date): bool
    {
        $date = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $date->between($this->start_date, $this->end_date);
    }
}
