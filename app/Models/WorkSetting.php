<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * WorkSetting Model
 *
 * Global work tolerance settings for attendance system.
 */
class WorkSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'before_check_in',
        'after_check_in',
        'late_limit',
        'before_check_out',
        'require_check_in',
        'fine_tier1_amount',
        'fine_tier2_amount',
        'fine_tier1_max_minutes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'require_check_in' => 'boolean',
            'fine_tier1_amount' => 'integer',
            'fine_tier2_amount' => 'integer',
            'fine_tier1_max_minutes' => 'integer',
        ];
    }

    /**
     * Get the current work settings (singleton pattern).
     */
    public static function current(): self
    {
        return self::firstOrCreate([], [
            'before_check_in' => 60,
            'after_check_in' => 10,
            'late_limit' => 120,
            'before_check_out' => 30,
            'require_check_in' => true,
            'fine_tier1_amount' => 5000,
            'fine_tier2_amount' => 10000,
            'fine_tier1_max_minutes' => 15,
        ]);
    }
}
