<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Office Model - Represents a physical office location for geofencing.
 *
 * @property int $id
 * @property string $name
 * @property float $latitude
 * @property float $longitude
 * @property int $radius_meters
 */
class Office extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'radius_meters' => 'integer',
        ];
    }

    /**
     * Get all users assigned to this office.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
