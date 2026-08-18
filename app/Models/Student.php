<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    public const LEVELS = ['mi', 'smp'];

    public const STATUSES = ['Aktif', 'Tidak Aktif', 'Lulus', 'Pindah', 'Keluar'];

    protected $fillable = [
        'school_class_id', 'school_level', 'source', 'external_id', 'nama_lengkap', 'nisn', 'nik',
        'tempat_lahir', 'tanggal_lahir', 'tingkat_rombel', 'status', 'jenis_kelamin', 'alamat',
        'no_telepon', 'kebutuhan_khusus', 'disabilitas', 'nomor_kip_pip', 'nama_ayah_kandung',
        'nama_ibu_kandung', 'nama_wali', 'last_synced_at',
    ];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date', 'last_synced_at' => 'datetime'];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
