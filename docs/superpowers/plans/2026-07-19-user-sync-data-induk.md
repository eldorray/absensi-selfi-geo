# User Sync from Data Induk API — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Admin menarik data pegawai dari API data induk lewat satu tombol di Kelola User; user dibuat/diperbarui tanpa duplikat.

**Architecture:** Controller tipis memanggil `UserSyncService` yang melakukan loop paginasi HTTP ke `{base}/api/{source}/all`, memetakan field, mencocokkan by NIP, lalu create/update `User`. Identitas disimpan di kolom baru `nip`/`nik`. Base URL dari config/env.

**Tech Stack:** Laravel 12, Pest, Laravel HTTP Client (`Http::fake`), Blade, Alpine (tak wajib), Vite.

## Global Constraints

- PHP: `declare(strict_types=1);` di setiap file PHP baru; ketik parameter/return/property.
- `$fillable` eksplisit; JANGAN `User::create($request->all())`.
- Logika bisnis di Service, controller tipis.
- Format kode wajib lolos `./vendor/bin/pint` dan `./vendor/bin/phpstan analyse` tanpa error baru.
- Test: Pest, `RefreshDatabase` sudah global (`tests/Pest.php`), DB sqlite `:memory:`.
- Sumber sync tetap: `guru-mi`, `guru-smp`. Email user baru: `Str::slug($nip, '.').'@guru.local'`. Password baru: `Hash::make($nip)`. Role user baru: slug `guru`, `office_id` null.
- Base URL: `config('services.data_induk.base_url')`, default `https://datainduk.ypdhalmadani.sch.id`.
- Update user existing: hanya `name` + `nik`. JANGAN sentuh role/office/email/password.

---

### Task 1: Kolom Identitas nip/nik di users

**Files:**
- Create: `database/migrations/2026_07_19_000000_add_nip_nik_to_users_table.php`
- Modify: `app/Models/User.php:23-30` (tambah ke `$fillable`)
- Test: `tests/Feature/Admin/UserSyncTest.php` (dibuat di sini, diperluas di task berikut)

**Interfaces:**
- Produces: kolom `users.nip` (string, nullable, unique), `users.nik` (string, nullable, unique); `User` mass-assignable untuk `nip`, `nik`.

- [ ] **Step 1: Tulis test gagal (kolom & mass assignment)**

Buat `tests/Feature/Admin/UserSyncTest.php`:

```php
<?php

use App\Models\Role;
use App\Models\User;

test('user has mass-assignable nip and nik columns', function () {
    $role = Role::create(['name' => 'Guru', 'slug' => 'guru', 'is_admin' => false]);

    $user = User::create([
        'name' => 'Budi',
        'email' => 'budi@guru.local',
        'password' => 'secret-hash',
        'role_id' => $role->id,
        'nip' => '199001011000',
        'nik' => '3200010101900001',
    ]);

    expect($user->fresh())
        ->nip->toBe('199001011000')
        ->nik->toBe('3200010101900001');
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test tests/Feature/Admin/UserSyncTest.php`
Expected: FAIL — kolom `nip`/`nik` tidak ada (SQLSTATE no such column) atau tak ter-fill.

- [ ] **Step 3: Buat migration**

`database/migrations/2026_07_19_000000_add_nip_nik_to_users_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->nullable()->unique()->after('email');
            $table->string('nik')->nullable()->unique()->after('nip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nip']);
            $table->dropUnique(['nik']);
            $table->dropColumn(['nip', 'nik']);
        });
    }
};
```

- [ ] **Step 4: Tambah `nip`, `nik` ke `$fillable`**

Di `app/Models/User.php`, ubah array `$fillable` menjadi:

```php
    protected $fillable = [
        'name',
        'email',
        'password',
        'office_id',
        'role_id',
        'avatar_path',
        'nip',
        'nik',
    ];
```

- [ ] **Step 5: Jalankan test, pastikan lulus**

Run: `php artisan test tests/Feature/Admin/UserSyncTest.php`
Expected: PASS (1 test).

- [ ] **Step 6: Pint + PHPStan**

Run: `./vendor/bin/pint && ./vendor/bin/phpstan analyse`
Expected: exit 0, tak ada error baru.

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_07_19_000000_add_nip_nik_to_users_table.php app/Models/User.php tests/Feature/Admin/UserSyncTest.php
git commit -m "feat: add nip and nik identity columns to users"
```

---

### Task 2: UserSyncService

**Files:**
- Create: `app/Services/UserSyncService.php`
- Modify: `config/services.php` (tambah blok `data_induk`)
- Modify: `.env.example` (tambah `SYNC_API_BASE_URL`)
- Test: `tests/Feature/Admin/UserSyncTest.php` (perluas)

**Interfaces:**
- Consumes: `User` dengan kolom `nip`/`nik` (Task 1); `Role` (slug `guru`); `config('services.data_induk.base_url')`.
- Produces: `App\Services\UserSyncService::sync(): array` mengembalikan `array{created:int, updated:int, failed:int, errors:list<string>}`. Melempar `RuntimeException` bila response non-2xx, format tak valid, atau role `guru` tidak ada. `Illuminate\Http\Client\ConnectionException` merambat ke pemanggil.

- [ ] **Step 1: Tambah config data_induk**

Di `config/services.php`, sebelum penutup `];`, tambahkan:

```php
    'data_induk' => [
        'base_url' => env('SYNC_API_BASE_URL', 'https://datainduk.ypdhalmadani.sch.id'),
    ],
```

Di `.env.example`, tambahkan baris:

```
SYNC_API_BASE_URL=https://datainduk.ypdhalmadani.sch.id
```

- [ ] **Step 2: Tulis test happy-path (create) gagal**

Tambahkan ke `tests/Feature/Admin/UserSyncTest.php` (di atas file, setelah `use`, tambahkan `use App\Services\UserSyncService;` dan `use Illuminate\Support\Facades\Http;` serta `use Illuminate\Support\Facades\Hash;`):

```php
function guruRole(): Role
{
    return Role::firstOrCreate(
        ['slug' => 'guru'],
        ['name' => 'Guru', 'is_admin' => false],
    );
}

test('sync creates new users from both sources with guru role and nip password', function () {
    guruRole();

    Http::fake([
        '*/api/guru-mi/all*' => Http::response([
            'data' => [
                ['full_name' => 'Budi MI', 'nik' => '3201010101900001'],
            ],
            'current_page' => 1,
            'last_page' => 1,
        ], 200),
        '*/api/guru-smp/all*' => Http::response([
            'data' => [
                ['full_name' => 'Siti SMP', 'nik' => '3201010101900002'],
            ],
            'current_page' => 1,
            'last_page' => 1,
        ], 200),
    ]);

    $result = app(UserSyncService::class)->sync();

    expect($result)
        ->created->toBe(2)
        ->updated->toBe(0)
        ->failed->toBe(0);

    $budi = User::where('nip', '3201010101900001')->first();
    expect($budi)->not->toBeNull()
        ->and($budi->name)->toBe('Budi MI')
        ->and($budi->email)->toBe('3201010101900001@guru.local')
        ->and($budi->role->slug)->toBe('guru')
        ->and($budi->office_id)->toBeNull()
        ->and(Hash::check('3201010101900001', $budi->password))->toBeTrue();
});
```

Catatan: `$result` mengembalikan array; Pest `expect($array)->created->toBe(...)` bekerja untuk key array via property accessor. Jika accessor array tidak didukung di versi Pest ini, ganti ke `expect($result['created'])->toBe(2)` dst. (implementer verifikasi saat run).

- [ ] **Step 3: Jalankan, pastikan gagal**

Run: `php artisan test tests/Feature/Admin/UserSyncTest.php`
Expected: FAIL — `Class "App\Services\UserSyncService" not found`.

- [ ] **Step 4: Implementasi UserSyncService**

`app/Services/UserSyncService.php`:

```php
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
```

- [ ] **Step 5: Jalankan test, pastikan lulus**

Run: `php artisan test tests/Feature/Admin/UserSyncTest.php`
Expected: PASS (2 test).

- [ ] **Step 6: Tambah test idempotent + tidak menimpa manual**

Tambahkan ke file test:

```php
test('re-sync updates existing user by nip without duplicating', function () {
    guruRole();

    $payload = fn (string $name) => [
        '*/api/guru-mi/all*' => Http::response([
            'data' => [['full_name' => $name, 'nik' => '3201010101900001']],
            'current_page' => 1, 'last_page' => 1,
        ], 200),
        '*/api/guru-smp/all*' => Http::response([
            'data' => [], 'current_page' => 1, 'last_page' => 1,
        ], 200),
    ];

    Http::fake($payload('Budi Awal'));
    app(UserSyncService::class)->sync();

    Http::fake($payload('Budi Revisi'));
    $result = app(UserSyncService::class)->sync();

    expect(User::where('nip', '3201010101900001')->count())->toBe(1)
        ->and($result['created'])->toBe(0)
        ->and($result['updated'])->toBe(1)
        ->and(User::where('nip', '3201010101900001')->first()->name)->toBe('Budi Revisi');
});

test('re-sync does not overwrite manually assigned role and office', function () {
    $guru = guruRole();
    $adminRole = Role::create(['name' => 'Administrator', 'slug' => 'administrator', 'is_admin' => true]);
    $office = \App\Models\Office::create([
        'name' => 'Kantor Pusat',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);

    $existing = User::create([
        'name' => 'Budi Lama',
        'email' => 'budi.custom@sekolah.id',
        'password' => Hash::make('rahasia'),
        'role_id' => $adminRole->id,
        'office_id' => $office->id,
        'nip' => '3201010101900001',
        'nik' => '3201010101900001',
    ]);

    Http::fake([
        '*/api/guru-mi/all*' => Http::response([
            'data' => [['full_name' => 'Budi Baru', 'nik' => '3201010101900001']],
            'current_page' => 1, 'last_page' => 1,
        ], 200),
        '*/api/guru-smp/all*' => Http::response([
            'data' => [], 'current_page' => 1, 'last_page' => 1,
        ], 200),
    ]);

    app(UserSyncService::class)->sync();

    $existing->refresh();
    expect($existing->name)->toBe('Budi Baru')
        ->and($existing->role_id)->toBe($adminRole->id)
        ->and($existing->office_id)->toBe($office->id)
        ->and($existing->email)->toBe('budi.custom@sekolah.id')
        ->and(Hash::check('rahasia', $existing->password))->toBeTrue();
});
```

Catatan implementer: verifikasi field wajib `Office::create` sesuai `$fillable` model `Office` (name, latitude, longitude, radius_meters). Sesuaikan bila berbeda.

- [ ] **Step 7: Jalankan test, pastikan lulus**

Run: `php artisan test tests/Feature/Admin/UserSyncTest.php`
Expected: PASS (4 test).

- [ ] **Step 8: Pint + PHPStan**

Run: `./vendor/bin/pint && ./vendor/bin/phpstan analyse`
Expected: exit 0.

- [ ] **Step 9: Commit**

```bash
git add app/Services/UserSyncService.php config/services.php .env.example tests/Feature/Admin/UserSyncTest.php
git commit -m "feat: add UserSyncService for data induk sync"
```

---

### Task 3: Controller + Route

**Files:**
- Modify: `app/Http/Controllers/Admin/UserController.php` (tambah `syncFromApi`)
- Modify: `routes/web.php:82` (tambah route sebelum `Route::resource('users', ...)`)
- Test: `tests/Feature/Admin/UserSyncTest.php` (perluas)

**Interfaces:**
- Consumes: `UserSyncService::sync()` (Task 2).
- Produces: route `admin.users.sync` (POST `admin/users/sync`) → `UserController::syncFromApi(UserSyncService $service): RedirectResponse`. Flash `success`/`error`.

- [ ] **Step 1: Tulis test endpoint (auth admin + non-admin) gagal**

Tambahkan ke file test (tambahkan `use` bila perlu):

```php
function adminUser(): User
{
    $role = Role::firstOrCreate(
        ['slug' => 'administrator'],
        ['name' => 'Administrator', 'is_admin' => true],
    );

    return User::factory()->create(['role_id' => $role->id]);
}

test('admin can trigger user sync and sees a success flash', function () {
    guruRole();

    Http::fake([
        '*/api/guru-mi/all*' => Http::response([
            'data' => [['full_name' => 'Budi MI', 'nik' => '3201010101900001']],
            'current_page' => 1, 'last_page' => 1,
        ], 200),
        '*/api/guru-smp/all*' => Http::response([
            'data' => [], 'current_page' => 1, 'last_page' => 1,
        ], 200),
    ]);

    $this->actingAs(adminUser())
        ->post(route('admin.users.sync'))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(User::where('nip', '3201010101900001')->exists())->toBeTrue();
});

test('non-admin cannot trigger user sync', function () {
    guruRole();

    $employee = User::factory()->create([
        'role_id' => Role::create(['name' => 'Guru2', 'slug' => 'guru2', 'is_admin' => false])->id,
    ]);

    $this->actingAs($employee)
        ->post(route('admin.users.sync'))
        ->assertForbidden();

    Http::assertNothingSent();
});

test('connection failure shows an error flash and creates no users', function () {
    guruRole();

    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('down');
    });

    $this->actingAs(adminUser())
        ->post(route('admin.users.sync'))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(User::whereNotNull('nip')->count())->toBe(0);
});
```

Catatan: `assertForbidden()` mengharap `AdminMiddleware` mengembalikan 403 untuk non-admin. Bila middleware me-redirect (bukan 403), ganti assertion ke `->assertRedirect()` dan verifikasi tak ada user dibuat. Implementer sesuaikan setelah cek `AdminMiddleware`.

- [ ] **Step 2: Jalankan, pastikan gagal**

Run: `php artisan test tests/Feature/Admin/UserSyncTest.php`
Expected: FAIL — route `admin.users.sync` belum ada (RouteNotFoundException).

- [ ] **Step 3: Tambah route**

Di `routes/web.php`, di dalam grup admin, tepat sebelum baris `Route::resource('users', Admin\UserController::class)->except(['show']);`, tambahkan:

```php
    Route::post('users/sync', [Admin\UserController::class, 'syncFromApi'])->name('users.sync');
```

- [ ] **Step 4: Tambah method controller**

Di `app/Http/Controllers/Admin/UserController.php`, tambahkan import di atas:

```php
use App\Services\UserSyncService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;
```

Tambahkan method (mis. setelah `store`):

```php
    /**
     * Sync users from the data induk API.
     */
    public function syncFromApi(UserSyncService $service): RedirectResponse
    {
        try {
            $result = $service->sync();
        } catch (ConnectionException $e) {
            Log::error('User sync connection error: '.$e->getMessage());

            return back()->with('error', 'Tidak dapat terhubung ke API data induk.');
        } catch (\RuntimeException $e) {
            Log::error('User sync error: '.$e->getMessage());

            return back()->with('error', $e->getMessage());
        }

        $message = "Sync selesai: {$result['created']} user baru, {$result['updated']} diperbarui";
        $message .= $result['failed'] > 0 ? ", {$result['failed']} gagal." : '.';

        return back()->with('success', $message);
    }
```

- [ ] **Step 5: Jalankan test, pastikan lulus**

Run: `php artisan test tests/Feature/Admin/UserSyncTest.php`
Expected: PASS (7 test).

- [ ] **Step 6: Pint + PHPStan**

Run: `./vendor/bin/pint && ./vendor/bin/phpstan analyse`
Expected: exit 0.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/UserController.php routes/web.php tests/Feature/Admin/UserSyncTest.php
git commit -m "feat: add admin user sync endpoint"
```

---

### Task 4: Tombol UI di Kelola User

**Files:**
- Modify: `resources/views/admin/users/index.blade.php` (header actions)

**Interfaces:**
- Consumes: route `admin.users.sync` (Task 3); flash `success`/`error` sudah dirender di halaman ini (baris 15-38).

- [ ] **Step 1: Tambah tombol Sync di header**

Di `resources/views/admin/users/index.blade.php`, di dalam slot aksi `<x-admin.page-header>` (tempat tombol "Tambah User"), tambahkan form sync **sebelum** link "Tambah User":

```blade
            <form action="{{ route('admin.users.sync') }}" method="POST"
                onsubmit="return confirm('Tarik data pegawai dari API data induk? Ini membuat/memperbarui user.')">
                @csrf
                <button type="submit"
                    class="admin-button-secondary inline-flex items-center gap-2 px-4 py-2 text-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Sync Data Induk
                </button>
            </form>
```

- [ ] **Step 2: Build aset & cek compile Blade**

Run: `npm run build && php artisan view:clear && php artisan view:cache`
Expected: Vite exit 0; view cache sukses tanpa error Blade.
(Catatan: bila shim `npm` bermasalah, pakai path node langsung, mis. `/opt/homebrew/bin/npm run build`.)

- [ ] **Step 3: Verifikasi manual singkat**

Run: `php artisan serve` lalu buka `/admin/users`. Pastikan tombol "Sync Data Induk" tampil di sebelah "Tambah User", dan `confirm()` muncul saat diklik. (Tanpa server API asli, klik akan menghasilkan flash error koneksi — itu perilaku benar.)

- [ ] **Step 4: Commit**

```bash
git add resources/views/admin/users/index.blade.php
git commit -m "feat: add data induk sync button to users page"
```

---

### Task 5: Verifikasi Akhir

**Files:** hanya perbaikan bila ada temuan.

- [ ] **Step 1: Suite penuh + kualitas**

Run:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

Expected: semua test hijau; Pint tak ada file yang perlu diformat; PHPStan tanpa error baru.

- [ ] **Step 2: Cek env & config nyata**

Run:

```bash
php artisan config:clear
php artisan tinker --execute="echo config('services.data_induk.base_url');"
```

Expected: mencetak `https://datainduk.ypdhalmadani.sch.id` (atau nilai `SYNC_API_BASE_URL` bila diset).

- [ ] **Step 3: Commit perbaikan bila ada**

```bash
git add -A
git commit -m "fix: polish user sync verification findings"
```

(Bila tak ada perubahan, lewati — jangan commit kosong.)

---

## Self-Review (penulis plan)

- **Spec coverage:** kolom nip/nik (Task 1) ✓; service loop/paginasi/mapping/match/create-update (Task 2) ✓; config+env (Task 2) ✓; controller+route+otorisasi (Task 3) ✓; tombol UI (Task 4) ✓; 5 skenario test spec (create, idempotent, no-overwrite, otorisasi, connection error) tercakup di Task 2-3 ✓; risiko password/email tercatat di spec (tak butuh task) ✓.
- **Placeholder scan:** tak ada TBD/TODO; semua step berisi kode nyata.
- **Type consistency:** `sync(): array{created,updated,failed,errors}` konsisten dipakai controller (`$result['created']` dll). `upsertRow` mengembalikan `'created'|'updated'|'failed'` cocok dengan `match`. Nama route `admin.users.sync` konsisten di route, test, blade.
