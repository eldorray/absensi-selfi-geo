<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Announcement Model - Dynamic info cards shown to employees on their dashboard.
 */
class Announcement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'office_id',
        'title',
        'summary',
        'body',
        'image_path',
        'is_active',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The office this announcement targets, or null when it is global.
     *
     * @return BelongsTo<Office, $this>
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Scope: only active announcements, ordered for display.
     */
    public function scopeActiveOrdered(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    /**
     * Scope: announcements visible to a given office — the office's own plus
     * global (office_id null). Null office sees only global announcements.
     *
     * @param  Builder<Announcement>  $query
     * @return Builder<Announcement>
     */
    public function scopeVisibleToOffice(Builder $query, ?int $officeId): Builder
    {
        return $query->where(function (Builder $q) use ($officeId): void {
            $q->whereNull('office_id');
            if ($officeId !== null) {
                $q->orWhere('office_id', $officeId);
            }
        });
    }

    /**
     * Public URL for the card image, or null when none uploaded.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
