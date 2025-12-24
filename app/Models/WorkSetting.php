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
        ]);
    }
}
