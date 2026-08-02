<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attendance Model - Records user check-ins and check-outs with selfie and geolocation.
 *
 * @property AttendanceStatus $status
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $check_out_at
 * @property ?string $image_path
 * @property ?string $check_out_image_path
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
        'client_uuid',
        'synced_at',
        'check_out_client_uuid',
        'check_out_synced_at',
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
            'synced_at' => 'datetime',
            'check_out_synced_at' => 'datetime',
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
        return asset('storage/'.$this->image_path);
    }

    /**
     * Get the full URL to the check-out selfie image.
     */
    public function getCheckOutImageUrlAttribute(): ?string
    {
        return $this->check_out_image_path
            ? asset('storage/'.$this->check_out_image_path)
            : null;
    }

    /**
     * Check if user has checked out.
     */
    public function hasCheckedOut(): bool
    {
        return $this->check_out_at !== null;
    }

    /**
     * Keterangan sinkronisasi offline untuk admin, atau null bila baris ini
     * sepenuhnya online.
     *
     * Menyebut BAGIAN MANA yang datang dari antrean: absen masuk dan absen
     * pulang punya penanda masing-masing (`synced_at`, `check_out_synced_at`),
     * dan satu baris bisa punya salah satu saja — mis. masuk online lalu pulang
     * dari antrean. Tanda "Offline" tanpa rincian tak bisa membedakannya, jadi
     * jam absen mana yang berasal dari perangkat tidak dapat ditelusuri saat
     * ada sengketa.
     */
    public function offlineSyncNote(): ?string
    {
        $parts = [];

        if ($this->synced_at !== null) {
            $parts[] = 'masuk '.$this->synced_at->format('d M Y H:i');
        }

        if ($this->check_out_synced_at !== null) {
            $parts[] = 'pulang '.$this->check_out_synced_at->format('d M Y H:i');
        }

        return $parts === [] ? null : 'Dikirim dari antrean offline — '.implode('; ', $parts);
    }
}
