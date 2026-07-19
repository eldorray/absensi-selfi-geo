<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Menyinkronkan data pegawai dari API data induk ke tabel users.
 */
class UserSyncService
{
    /** @var list<string> */
    private const SOURCES = ['guru-mi', 'guru-smp'];

    private const EMAIL_DOMAIN = '@guru.local';

    private const MAX_PAGES = 1000;

    private const REQUEST_TIMEOUT = 60;

    /**
     * @return array{created:int, updated:int, failed:int, errors:list<string>}
     */
    public function sync(): array
    {
        $guruRole = Role::where('slug', 'guru')->first();

        if (! $guruRole) {
            throw new RuntimeException("Role 'guru' belum ada. Seed role terlebih dahulu.");
        }

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        $baseUrl = (string) config('services.data_induk.base_url');

        foreach (self::SOURCES as $source) {
            $page = 1;

            while (true) {
                $response = Http::timeout(self::REQUEST_TIMEOUT)
                    ->get("{$baseUrl}/api/{$source}/all", ['page' => $page]);

                if (! $response->successful()) {
                    throw new RuntimeException(
                        "Gagal mengambil data dari API ({$source}). Status: {$response->status()}"
                    );
                }

                $data = $response->json();
                $rows = $data['data'] ?? $data;

                if (! is_array($rows)) {
                    throw new RuntimeException('Format response API tidak valid.');
                }

                foreach ($rows as $row) {
                    $outcome = $this->upsertRow($row, $guruRole->id, $errors);

                    match ($outcome) {
                        'created' => $created++,
                        'updated' => $updated++,
                        default => $failed++,
                    };
                }

                $lastPage = (int) ($data['last_page'] ?? 1);
                $currentPage = (int) ($data['current_page'] ?? $page);
                $nextPageUrl = $data['next_page_url'] ?? null;

                if ($nextPageUrl === null && $currentPage >= $lastPage) {
                    break;
                }

                $page++;

                if ($page > self::MAX_PAGES) {
                    break;
                }
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  list<string>  $errors
     * @return 'created'|'updated'|'failed'
     */
    private function upsertRow(array $row, int $guruRoleId, array &$errors): string
    {
        $nama = $row['full_name'] ?? $row['nama'] ?? null;
        $nip = $row['nik'] ?? null;
        $nik = $row['nik'] ?? null;

        if (! is_string($nama) || $nama === '' || ! is_string($nip) || $nip === '') {
            $errors[] = 'Data tidak lengkap: nama/nik kosong.';

            return 'failed';
        }

        try {
            $existing = User::where('nip', $nip)->first();

            if ($existing) {
                $existing->update(['name' => $nama, 'nik' => $nik]);

                return 'updated';
            }

            User::create([
                'name' => $nama,
                'email' => Str::slug($nip, '.').self::EMAIL_DOMAIN,
                'password' => Hash::make($nip),
                'role_id' => $guruRoleId,
                'office_id' => null,
                'nip' => $nip,
                'nik' => $nik,
            ]);

            return 'created';
        } catch (QueryException $e) {
            $errors[] = "Gagal menyimpan {$nama} (nip {$nip}): {$e->getMessage()}";

            return 'failed';
        }
    }
}
