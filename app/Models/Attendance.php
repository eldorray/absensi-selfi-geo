<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attendance Model - Records user check-ins and check-outs with selfie and geolocation.
 */
class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'status',
        'image_path',
        'check_in_lat',
        'check_in_long',
        'distance_meters',
        'check_out_at',
        'check_out_lat',
        'check_out_long',
        'check_out_image_path',
        'check_out_distance_meters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AttendanceStatus::class,
            'check_in_lat' => 'decimal:8',
            'check_in_long' => 'decimal:8',
            'distance_meters' => 'float',
            'check_out_at' => 'datetime',
            'check_out_lat' => 'decimal:8',
            'check_out_long' => 'decimal:8',
            'check_out_distance_meters' => 'float',
        ];
    }

    /**
     * Get the user who made this attendance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the academic year this attendance belongs to.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the full URL to the check-in selfie image.
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    /**
     * Get the full URL to the check-out selfie image.
     */
    public function getCheckOutImageUrlAttribute(): ?string
    {
        return $this->check_out_image_path 
            ? asset('storage/' . $this->check_out_image_path) 
            : null;
    }

    /**
     * Check if user has checked out.
     */
    public function hasCheckedOut(): bool
    {
        return $this->check_out_at !== null;
    }
}

