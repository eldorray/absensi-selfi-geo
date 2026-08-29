<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StudentSyncService
{
    private const REQUEST_TIMEOUT = 60;

    /** @return array{created:int, updated:int, skipped:int} */
    public function sync(string $schoolLevel): array
    {
        $this->assertSchoolLevel($schoolLevel);

        $baseUrl = rtrim((string) config('services.data_induk.base_url'), '/');
        $response = Http::timeout(self::REQUEST_TIMEOUT)->get("{$baseUrl}/api/siswa-{$schoolLevel}/all");

        if (! $response->successful()) {
            throw new RuntimeException('API data induk mengembalikan status '.$response->status().'.');
        }

        $payload = $response->json();
        $rows = is_array($payload) ? ($payload['data'] ?? null) : null;

        if (! is_array($rows)) {
            throw new RuntimeException('Format respons API data induk tidak valid.');
        }

        return DB::transaction(function () use ($rows, $schoolLevel): array {
            $summary = ['created' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($rows as $row) {
                if (! is_array($row)) {
                    $summary['skipped']++;

                    continue;
                }

                $nisn = $this->nullableString($row['nisn'] ?? null);
                $nik = $this->nullableString($row['nik'] ?? null);

                if ($nisn === null && $nik === null) {
                    $summary['skipped']++;

                    continue;
                }

                $student = $nisn === null
                    ? null
                    : Student::query()->where('nisn', $nisn)->first();

                if ($student === null && $nik !== null) {
                    $student = Student::query()->where('nik', $nik)->first();
                }

                if ($student !== null && $student->school_level !== $schoolLevel) {
                    $summary['skipped']++;

                    continue;
                }

                $className = $this->nullableString($row['tingkat_rombel'] ?? null);
                $schoolClass = $className === null ? null : SchoolClass::query()->firstOrCreate(
                    ['school_level' => $schoolLevel, 'normalized_name' => SchoolClass::normalizeName($className)],
                    ['name' => $className, 'grade_level' => $this->extractGradeLevel($className), 'is_active' => true],
                );

                $attributes = Arr::only($row, [
                    'nama_lengkap', 'tempat_lahir', 'tanggal_lahir', 'status', 'jenis_kelamin', 'alamat',
                    'no_telepon', 'kebutuhan_khusus', 'disabilitas', 'nomor_kip_pip', 'nama_ayah_kandung',
                    'nama_ibu_kandung', 'nama_wali',
                ]);
                $attributes = array_map(fn ($value) => $this->nullableString($value), $attributes);
                $attributes['nama_lengkap'] = $attributes['nama_lengkap'] ?? 'Tanpa Nama';
                $attributes['status'] = $attributes['status'] ?? 'Aktif';
                $attributes['school_level'] = $schoolLevel;
                $attributes['school_class_id'] = $schoolClass?->id;
                $attributes['source'] = 'api';
                $externalId = $this->nullableString($row['id'] ?? null);
                $attributes['external_id'] = $externalId;
                $attributes['nisn'] = $nisn;
                $attributes['nik'] = $nik;
                $attributes['tingkat_rombel'] = $className;
                $attributes['last_synced_at'] = now();

                // ponytail: external_id data induk bisa dipakai ulang setelah import ulang
                // di sana, jadi lepas pemegang lama sebelum klaim. Identitas asli tetap nisn/nik.
                if ($externalId !== null) {
                    Student::query()
                        ->where('school_level', $schoolLevel)
                        ->where('external_id', $externalId)
                        ->when($student !== null, fn ($query) => $query->whereKeyNot($student->getKey()))
                        ->update(['external_id' => null]);
                }

                if ($student === null) {
                    Student::query()->create($attributes);
                    $summary['created']++;
                } else {
                    $student->update($attributes);
                    $summary['updated']++;
                }
            }

            return $summary;
        });
    }

    private function assertSchoolLevel(string $schoolLevel): void
    {
        if (! in_array($schoolLevel, Student::LEVELS, true)) {
            throw new RuntimeException('Jenjang sekolah tidak valid.');
        }
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function extractGradeLevel(string $className): ?int
    {
        preg_match('/\d+/', $className, $matches);
        $grade = isset($matches[0]) ? (int) $matches[0] : null;

        return $grade !== null && $grade >= 1 && $grade <= 9 ? $grade : null;
    }
}
