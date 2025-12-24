<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Role Model - Defines user roles in the system.
 */
class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'is_admin',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get all users with this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get badge CSS class for the role.
     */
    public function getBadgeClassAttribute(): string
    {
        if ($this->is_admin) {
            return 'bg-red-100 text-red-800';
        }

        return match ($this->slug) {
            'kepala-sekolah' => 'bg-purple-100 text-purple-800',
            'guru' => 'bg-blue-100 text-blue-800',
            'tendik' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
