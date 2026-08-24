<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property ?int $school_class_id
 * @property string $school_level
 * @property ?string $source
 * @property ?string $external_id
 * @property string $nama_lengkap
 * @property ?string $nisn
 * @property ?string $nik
 * @property ?string $tempat_lahir
 * @property ?\Illuminate\Support\Carbon $tanggal_lahir
 * @property ?string $tingkat_rombel
 * @property string $status
 * @property ?string $jenis_kelamin
 * @property ?string $alamat
 * @property ?string $no_telepon
 * @property ?string $kebutuhan_khusus
 * @property ?string $disabilitas
 * @property ?string $nomor_kip_pip
 * @property ?string $nama_ayah_kandung
 * @property ?string $nama_ibu_kandung
 * @property ?string $nama_wali
 * @property ?\Illuminate\Support\Carbon $last_synced_at
 * @property-read ?SchoolClass $schoolClass
 * @property-read \Illuminate\Database\Eloquent\Collection<int, BkRecord> $bkRecords
 * @property-read \Illuminate\Database\Eloquent\Collection<int, StudentReferral> $referrals
 * Diisi hanya oleh withCount(['bkRecords as violations_count' => ...]); null bila
 * query pemanggil tidak memintanya.
 * @property-read ?int $violations_count
 */
class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    public const LEVELS = ['mi', 'smp'];

    public const STATUSES = ['Aktif', 'Tidak Aktif', 'Lulus', 'Pindah', 'Keluar'];

    protected $fillable = [
        'school_class_id', 'school_level', 'source', 'external_id', 'nama_lengkap', 'nisn', 'nik',
        'tempat_lahir', 'tanggal_lahir', 'tingkat_rombel', 'status', 'jenis_kelamin', 'alamat',
        'no_telepon', 'kebutuhan_khusus', 'disabilitas', 'nomor_kip_pip', 'nama_ayah_kandung',
        'nama_ibu_kandung', 'nama_wali', 'last_synced_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date', 'last_synced_at' => 'datetime'];
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * @return HasMany<BkRecord, $this>
     */
    public function bkRecords(): HasMany
    {
        return $this->hasMany(BkRecord::class);
    }

    /**
     * @return HasMany<StudentReferral, $this>
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(StudentReferral::class);
    }
}
