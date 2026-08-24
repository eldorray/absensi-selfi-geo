<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Profil siswa untuk klien native.
 *
 * Dua bentuk dalam satu resource: baris daftar hanya memuat identitas ringkas,
 * sedangkan halaman detail menambahkan data pribadi. `$detailed` yang memisahkan
 * keduanya — bukan dua resource terpisah — supaya kontrak `id/nama/kelas` tidak
 * pernah menyimpang antara daftar dan detail.
 *
 * @property-read Student $resource
 */
class StudentResource extends JsonResource
{
    public function __construct(Student $resource, private readonly bool $detailed = false)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $student = $this->resource;

        $base = [
            'id' => $student->id,
            'nama_lengkap' => $student->nama_lengkap,
            'nisn' => $student->nisn,
            'school_level' => $student->school_level,
            'status' => $student->status,
            'class_name' => $student->schoolClass?->name,
            // withCount(['bkRecords as violations_count']) memberi atribut bernama
            // `violations_count`, bukan `violations_count_count`, jadi whenCounted()
            // tidak berlaku di sini — kehadiran atribut yang diperiksa langsung.
            'violations_count' => $this->when(
                $student->violations_count !== null,
                fn (): int => (int) $student->violations_count
            ),
        ];

        if (! $this->detailed) {
            return $base;
        }

        return $base + [
            'nik' => $student->nik,
            'jenis_kelamin' => $student->jenis_kelamin,
            'tempat_lahir' => $student->tempat_lahir,
            'tanggal_lahir' => $student->tanggal_lahir?->format('Y-m-d'),
            'tingkat_rombel' => $student->tingkat_rombel,
            'alamat' => $student->alamat,
            'no_telepon' => $student->no_telepon,
            'nama_ayah_kandung' => $student->nama_ayah_kandung,
            'nama_ibu_kandung' => $student->nama_ibu_kandung,
            'nama_wali' => $student->nama_wali,
        ];
    }
}
