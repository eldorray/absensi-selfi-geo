<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Leave Model - Represents leave/permission requests.
 */
class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user who requested the leave.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved/rejected the leave.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get leave type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'izin' => 'Izin',
            'cuti' => 'Cuti',
            'sakit' => 'Sakit',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get type badge class.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'izin' => 'bg-blue-100 text-blue-800',
            'cuti' => 'bg-purple-100 text-purple-800',
            'sakit' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get attachment URL.
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment) {
            return null;
        }
        return asset('storage/' . $this->attachment);
    }

    /**
     * Get duration in days.
     */
    public function getDurationAttribute(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    /**
     * Check if leave is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if leave is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if leave is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Scope to get pending leaves.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
