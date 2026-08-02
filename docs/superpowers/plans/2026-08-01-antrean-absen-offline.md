# Antrean Absen Offline — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Guru tetap bisa mencatat absen masuk/pulang saat tanpa sinyal; absennya tersimpan di perangkat dan terkirim otomatis dengan **jam kejadian sebenarnya** begitu koneksi kembali.

**Architecture:** Server menerima dua field opsional `captured_at` + `client_uuid`. Untuk entri offline, `created_at` ditulis sebagai waktu tangkap (sehingga semua jalur baca yang ada otomatis benar) dan `synced_at` mencatat kapan server menerimanya. Klien menyimpan antrean sebagai satu berkas JSON + berkas JPEG di app storage, lalu mengirim ulang saat app kembali ke depan atau jaringan pulih.

**Tech Stack:** Laravel 12 + Pest (backend), SwiftUI + Observation (iOS), Jetpack Compose + Retrofit + kotlinx.serialization (Android).

**Spec:** `docs/superpowers/specs/2026-08-01-antrean-absen-offline-design.md`

## Global Constraints

- Semua kolom migrasi **nullable** — baris lama dan jalur web/PWA harus tetap sah.
- Parameter waktu baru di `AttendanceService` **wajib** bernilai bawaan `now()`; `Employee\AttendanceController` (PWA) tidak boleh berubah perilaku.
- `captured_at` dan `client_uuid` **opsional** di API; request tanpa keduanya harus berperilaku persis seperti sekarang.
- Batas `captured_at`: tidak sebelum awal hari berjalan, tidak lebih dari 2 menit di masa depan, dinilai terhadap jam **server**.
- `client_uuid` yang diulang mengembalikan **200 berisi data tersimpan**, bukan 422.
- Antrean klien hanya menerima kegagalan **transport**; respons 422 membuang entri.
- Semua teks yang dilihat guru berbahasa Indonesia.
- Backend: `declare(strict_types=1);` di setiap berkas PHP baru, format dengan Pint sebelum commit.

---

## FASE 1 — BACKEND

### Task 1: Kolom antrean di tabel `attendances`

**Files:**
- Create: `database/migrations/2026_08_01_000000_add_offline_queue_to_attendances_table.php`
- Modify: `app/Models/Attendance.php:30-62`
- Test: `tests/Feature/Api/OfflineAttendanceTest.php`

**Interfaces:**
- Consumes: —
- Produces: kolom `client_uuid`, `synced_at`, `check_out_client_uuid`, `check_out_synced_at` pada model `Attendance`; keduanya `synced_at`/`check_out_synced_at` di-cast `datetime`.

- [ ] **Step 1: Write the failing test**

Buat `tests/Feature/Api/OfflineAttendanceTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function offlineTeacher(?Office $office = null): User
{
    $role = Role::firstOrCreate(['name' => 'employee'], ['display_name' => 'Employee']);
    $office ??= Office::create([
        'name' => 'MI Test',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);

    return User::create([
        'name' => 'Guru Offline',
        'email' => 'offline'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
        'office_id' => $office->id,
    ]);
}

test('attendance stores the offline queue columns', function () {
    $user = offlineTeacher();
    $syncedAt = Carbon::parse('2026-08-01 10:00:00');

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
        'client_uuid' => '11111111-1111-4111-8111-111111111111',
        'synced_at' => $syncedAt,
        'check_out_client_uuid' => '22222222-2222-4222-8222-222222222222',
        'check_out_synced_at' => $syncedAt,
    ])->fresh();

    expect($attendance->client_uuid)->toBe('11111111-1111-4111-8111-111111111111')
        ->and($attendance->check_out_client_uuid)->toBe('22222222-2222-4222-8222-222222222222')
        ->and($attendance->synced_at->format('Y-m-d H:i'))->toBe('2026-08-01 10:00')
        ->and($attendance->check_out_synced_at)->toBeInstanceOf(Carbon::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Api/OfflineAttendanceTest.php`
Expected: FAIL — kolom `client_uuid` tidak ada di tabel.

- [ ] **Step 3: Write the migration**

`database/migrations/2026_08_01_000000_add_offline_queue_to_attendances_table.php`:

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
        Schema::table('attendances', function (Blueprint $table): void {
            // Idempotensi kiriman dari antrean offline. Nullable: absen online
            // dan baris lama tidak punya uuid, dan MySQL/SQLite mengizinkan
            // banyak NULL pada unique index.
            $table->uuid('client_uuid')->nullable();
            $table->uuid('check_out_client_uuid')->nullable();

            // Non-null = baris ini datang dari antrean offline; nilainya jam
            // server saat menerima, sedangkan created_at memuat jam tangkap.
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('check_out_synced_at')->nullable();

            $table->unique(['user_id', 'client_uuid']);
            $table->unique(['user_id', 'check_out_client_uuid']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'client_uuid']);
            $table->dropUnique(['user_id', 'check_out_client_uuid']);
            $table->dropColumn([
                'client_uuid',
                'check_out_client_uuid',
                'synced_at',
                'check_out_synced_at',
            ]);
        });
    }
};
```

- [ ] **Step 4: Add the columns to the model**

Di `app/Models/Attendance.php`, tambahkan ke `$fillable` (setelah `'check_out_distance_meters',`):

```php
        'client_uuid',
        'synced_at',
        'check_out_client_uuid',
        'check_out_synced_at',
```

dan ke `casts()` (setelah `'check_out_distance_meters' => 'float',`):

```php
            'synced_at' => 'datetime',
            'check_out_synced_at' => 'datetime',
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/Api/OfflineAttendanceTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
./vendor/bin/pint --dirty
git add database/migrations/2026_08_01_000000_add_offline_queue_to_attendances_table.php app/Models/Attendance.php tests/Feature/Api/OfflineAttendanceTest.php
git commit -m "feat: add offline queue columns to attendances"
```

---

### Task 2: Parameter waktu di `AttendanceService`

**Files:**
- Modify: `app/Services/AttendanceService.php:68-118`
- Test: `tests/Feature/AttendanceServiceTimeTest.php`

**Interfaces:**
- Consumes: —
- Produces:
  - `checkInWindowError(User $user, ?Carbon $at = null): ?string`
  - `checkOutWindowError(User $user, ?Carbon $at = null): ?string`
  - `statusAt(User $user, ?Carbon $at = null): AttendanceStatus`
  - `statusNow(User $user): AttendanceStatus` (alias tipis, dipertahankan untuk pemanggil web)

- [ ] **Step 1: Write the failing test**

Buat `tests/Feature/AttendanceServiceTimeTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\AttendanceStatus;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSetting;
use App\Services\AttendanceService;
use Carbon\Carbon;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function timeTeacher(): User
{
    // Pola peran yang dipakai seluruh suite: `roles.slug` NOT NULL & unique,
    // dan tidak ada kolom `display_name`.
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = Office::create([
        'name' => 'MI Waktu',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);

    return User::create([
        'name' => 'Guru Waktu',
        'email' => 'waktu'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
        'office_id' => $office->id,
    ]);
}

afterEach(fn () => Carbon::setTestNow());

test('statusAt judges the supplied moment, not the clock', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 10:00:00'));
    $user = timeTeacher();
    $service = app(AttendanceService::class);
    $grace = WorkSetting::current()->after_check_in;

    $onTime = Carbon::parse('2026-08-03 07:00:00');
    $late = Carbon::parse('2026-08-03 07:00:00')->addMinutes($grace + 5);

    expect($service->statusAt($user, $onTime))->toBe(AttendanceStatus::Present)
        ->and($service->statusAt($user, $late))->toBe(AttendanceStatus::Late);
});

test('statusNow keeps judging the current clock', function () {
    $user = timeTeacher();
    $service = app(AttendanceService::class);
    $grace = WorkSetting::current()->after_check_in;

    Carbon::setTestNow(Carbon::parse('2026-08-03 07:00:00'));
    expect($service->statusNow($user))->toBe(AttendanceStatus::Present);

    Carbon::setTestNow(Carbon::parse('2026-08-03 07:00:00')->addMinutes($grace + 5));
    expect($service->statusNow($user))->toBe(AttendanceStatus::Late);
});

test('checkInWindowError judges the supplied moment', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 23:00:00'));
    $user = timeTeacher();
    $service = app(AttendanceService::class);

    // Jam server 23:00 sudah di luar jendela, tapi jam tangkap 07:00 masih di dalam.
    expect($service->checkInWindowError($user, Carbon::parse('2026-08-03 07:00:00')))->toBeNull()
        ->and($service->checkInWindowError($user))->not->toBeNull();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/AttendanceServiceTimeTest.php`
Expected: FAIL — `statusAt()` belum ada, `checkInWindowError()` hanya menerima satu argumen.

- [ ] **Step 3: Add the time parameters**

Di `app/Services/AttendanceService.php`, ganti ketiga method berikut:

```php
    /**
     * Why check-in is closed at a given moment, or null when it is open.
     *
     * The moment defaults to now, which keeps the Blade controller — and the
     * PWA behind it — behaving exactly as before. The mobile API passes the
     * device capture time so a queued check-in is judged when it happened.
     */
    public function checkInWindowError(User $user, ?Carbon $at = null): ?string
    {
        $settings = WorkSetting::current();
        $scheduled = $this->scheduledCheckIn($user);

        $earliest = $scheduled->copy()->subMinutes($settings->before_check_in);
        $latest = $scheduled->copy()->addMinutes($settings->late_limit);
        $moment = $at ?? now();

        if ($moment->lt($earliest)) {
            return 'Anda belum dapat absen. Waktu absen dimulai pukul '.$earliest->format('H:i').'.';
        }

        if ($moment->gt($latest)) {
            return 'Waktu absen sudah berakhir. Batas absen adalah pukul '.$latest->format('H:i').'.';
        }

        return null;
    }

    /**
     * Why check-out is still closed at a given moment, or null when it opened.
     */
    public function checkOutWindowError(User $user, ?Carbon $at = null): ?string
    {
        $opensAt = WorkSchedule::checkoutOpensAt(
            WorkSchedule::todayFor((int) $user->id),
            WorkSetting::current()->before_check_out,
        );

        if (($at ?? now())->lt($opensAt)) {
            return 'Belum waktunya absen pulang. Absen pulang dibuka pukul '.$opensAt->format('H:i').'.';
        }

        return null;
    }

    /**
     * On time or late at a given moment, judged against the schedule plus the
     * grace period.
     */
    public function statusAt(User $user, ?Carbon $at = null): AttendanceStatus
    {
        $lateAfter = $this->scheduledCheckIn($user)
            ->copy()
            ->addMinutes(WorkSetting::current()->after_check_in);

        return ($at ?? now())->gt($lateAfter) ? AttendanceStatus::Late : AttendanceStatus::Present;
    }

    /**
     * On time or late right now. Kept so existing callers stay untouched.
     */
    public function statusNow(User $user): AttendanceStatus
    {
        return $this->statusAt($user);
    }
```

- [ ] **Step 4: Run the new test plus the whole suite**

Run: `php artisan test tests/Feature/AttendanceServiceTimeTest.php`
Expected: PASS

Test `statusNow keeps judging the current clock` **adalah** jaminan PWA yang diminta spec: `Employee\AttendanceController` memanggil `statusNow()` dan `checkInWindowError($user)` tanpa argumen waktu, jadi selama test itu hijau, jalur web tak berubah perilaku.

Run: `php artisan test`
Expected: PASS — tak ada regresi pada jalur web/PWA.

- [ ] **Step 5: Commit**

```bash
./vendor/bin/pint --dirty
git add app/Services/AttendanceService.php tests/Feature/AttendanceServiceTimeTest.php
git commit -m "feat: judge attendance rules at a supplied moment"
```

---

### Task 3: Validasi `captured_at` + `client_uuid`

**Files:**
- Create: `app/Http/Requests/Api/Concerns/AcceptsCapturedAt.php`
- Modify: `app/Http/Requests/Api/StoreAttendanceRequest.php`
- Modify: `app/Http/Requests/Api/StoreCheckoutRequest.php`
- Test: `tests/Feature/Api/OfflineAttendanceTest.php` (tambah test)

**Interfaces:**
- Consumes: —
- Produces: trait `AcceptsCapturedAt` dengan `capturedAtRules(): array<string, list<string>>`, `capturedAtMessages(): array<string, string>`, `capturedAt(): ?Carbon`, `clientUuid(): ?string`; dipakai oleh kedua Form Request.

Aturan, pesan, dan batas toleransi jam hidup di satu tempat: dua salinan yang boleh berselisih adalah dua perilaku keamanan yang boleh berselisih.

- [ ] **Step 1: Write the failing test**

Tambahkan ke `tests/Feature/Api/OfflineAttendanceTest.php`:

```php
test('captured_at from a previous day is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-02 07:00:00',
        'client_uuid' => '33333333-3333-4333-8333-333333333333',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('captured_at');
    expect(Attendance::count())->toBe(0);

    Carbon::setTestNow();
});

test('captured_at in the future is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 08:10:00',
        'client_uuid' => '44444444-4444-4444-8444-444444444444',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('captured_at');

    Carbon::setTestNow();
});

test('captured_at without client_uuid is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 07:30:00',
    ])->assertStatus(422)->assertJsonValidationErrors('client_uuid');

    Carbon::setTestNow();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Api/OfflineAttendanceTest.php`
Expected: FAIL — field tak dikenal, tak ada error validasi yang diharapkan.

- [ ] **Step 3: Write the shared trait**

Buat `app/Http/Requests/Api/Concerns/AcceptsCapturedAt.php`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Concerns;

use Carbon\Carbon;

/**
 * Waktu tangkap dari antrean offline, dipakai absen masuk maupun pulang.
 *
 * Aturannya hidup di satu tempat: dua salinan yang boleh berselisih adalah
 * dua perilaku keamanan yang boleh berselisih.
 */
trait AcceptsCapturedAt
{
    /**
     * Toleransi selisih jam perangkat terhadap jam server, dalam menit.
     */
    public const CLOCK_SKEW_MINUTES = 2;

    /**
     * Dinilai terhadap jam server: jam perangkat justru yang sedang
     * diverifikasi, jadi tak boleh jadi alat verifikasinya sendiri.
     *
     * @return array<string, list<string>>
     */
    protected function capturedAtRules(): array
    {
        return [
            'captured_at' => [
                'nullable',
                'date',
                'after_or_equal:'.now()->startOfDay()->toDateTimeString(),
                'before_or_equal:'.now()->addMinutes(self::CLOCK_SKEW_MINUTES)->toDateTimeString(),
            ],
            'client_uuid' => ['nullable', 'uuid', 'required_with:captured_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function capturedAtMessages(): array
    {
        return [
            'captured_at.after_or_equal' => 'Absen tertunda hanya dapat dikirim pada hari yang sama.',
            'captured_at.before_or_equal' => 'Waktu absen tidak valid. Periksa jam pada perangkat Anda.',
            'client_uuid.required_with' => 'Kiriman absen tertunda harus menyertakan client_uuid.',
        ];
    }

    /**
     * Waktu tangkap di perangkat, atau null untuk absen online biasa.
     */
    public function capturedAt(): ?Carbon
    {
        $value = $this->validated('captured_at');

        return $value === null ? null : Carbon::parse($value);
    }

    public function clientUuid(): ?string
    {
        return $this->validated('client_uuid');
    }
}
```

- [ ] **Step 4: Use the trait in both Form Requests**

Di `app/Http/Requests/Api/StoreAttendanceRequest.php`, tambahkan `use App\Http\Requests\Api\Concerns\AcceptsCapturedAt;` di bagian atas dan `use AcceptsCapturedAt;` di dalam kelas, lalu gabungkan aturan & pesannya:

```php
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'mimetypes:image/jpeg,image/png', 'max:'.self::MAX_PHOTO_KB],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            ...$this->capturedAtRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.required' => 'Foto selfie diperlukan.',
            'photo.mimetypes' => 'Foto harus berupa gambar JPEG atau PNG.',
            'photo.max' => 'Ukuran foto maksimal 4 MB.',
            'latitude.required' => 'Lokasi GPS diperlukan.',
            'longitude.required' => 'Lokasi GPS diperlukan.',
            ...$this->capturedAtMessages(),
        ];
    }
```

Lakukan hal yang sama di `app/Http/Requests/Api/StoreCheckoutRequest.php`: tambahkan import + `use AcceptsCapturedAt;`, sebarkan `...$this->capturedAtRules()` ke dalam `rules()` yang ada dan `...$this->capturedAtMessages()` ke dalam `messages()` yang ada. Jangan ubah aturan lain di berkas itu.

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test tests/Feature/Api/OfflineAttendanceTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
./vendor/bin/pint --dirty
git add app/Http/Requests/Api/StoreAttendanceRequest.php app/Http/Requests/Api/StoreCheckoutRequest.php tests/Feature/Api/OfflineAttendanceTest.php
git commit -m "feat: validate the capture time on queued attendance"
```

---

### Task 4: Absen masuk memakai `captured_at` + idempoten

**Files:**
- Modify: `app/Http/Controllers/Api/AttendanceController.php:34-86`
- Test: `tests/Feature/Api/OfflineAttendanceTest.php` (tambah test)

**Interfaces:**
- Consumes: `AttendanceService::statusAt()`, `AttendanceService::checkInWindowError($user, $at)` (Task 2); `StoreAttendanceRequest::capturedAt()`, `::clientUuid()` (Task 3).
- Produces: perilaku `POST /api/attendance` yang menerima kiriman antrean.

- [ ] **Step 1: Write the failing test**

Tambahkan ke `tests/Feature/Api/OfflineAttendanceTest.php`:

```php
test('a queued check-in is stored at its capture time', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 10:00:00'));
    $user = offlineTeacher();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 07:00:00',
        'client_uuid' => '55555555-5555-4555-8555-555555555555',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('check_in_time', '07:00')
        ->assertJsonPath('status', 'on_time');

    $attendance = Attendance::first();
    expect($attendance->created_at->format('H:i'))->toBe('07:00')
        ->and($attendance->synced_at->format('H:i'))->toBe('10:00');

    Carbon::setTestNow();
});

test('replaying a client_uuid returns the stored record instead of a second row', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 08:00:00'));
    $user = offlineTeacher();
    $payload = [
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 07:00:00',
        'client_uuid' => '66666666-6666-4666-8666-666666666666',
    ];

    $first = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', $payload + [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
    ]);
    $second = $this->actingAs($user, 'sanctum')->postJson('/api/attendance', $payload + [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
    ]);

    $first->assertStatus(201);
    $second->assertStatus(200)->assertJsonPath('check_in_time', '07:00');
    expect(Attendance::count())->toBe(1);

    Carbon::setTestNow();
});

test('an online check-in still behaves exactly as before', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 07:05:00'));
    $user = offlineTeacher();

    $this->actingAs($user, 'sanctum')->postJson('/api/attendance', [
        'photo' => Illuminate\Http\UploadedFile::fake()->image('selfie.jpg'),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ])->assertStatus(201)->assertJsonPath('check_in_time', '07:05');

    $attendance = Attendance::first();
    expect($attendance->synced_at)->toBeNull()
        ->and($attendance->client_uuid)->toBeNull();

    Carbon::setTestNow();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Api/OfflineAttendanceTest.php`
Expected: FAIL — `check_in_time` berisi `10:00`, dan pengulangan uuid menghasilkan 422 "sudah melakukan absensi".

- [ ] **Step 3: Rewrite the store method**

Ganti method `store()` di `app/Http/Controllers/Api/AttendanceController.php`:

```php
    /**
     * Record a check-in for the signed-in teacher.
     *
     * A queued check-in carries `captured_at` (when the teacher actually stood
     * at the gate) and `client_uuid`. Every timing rule is judged against that
     * moment, and the row's created_at is written to it, so the dashboard and
     * history read the real time without any change on their side.
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $user = $request->user();
        $capturedAt = $request->capturedAt();
        $clientUuid = $request->clientUuid();
        $moment = $capturedAt ?? now();

        // A retry after a lost response must not read as a duplicate: the
        // teacher's attendance is already recorded, so hand it back as-is.
        if ($clientUuid !== null) {
            $existing = Attendance::query()
                ->where('user_id', $user->id)
                ->where('client_uuid', $clientUuid)
                ->first();

            if ($existing !== null) {
                return response()->json([
                    'status' => $existing->status->apiValue(),
                    'check_in_time' => $existing->created_at?->format('H:i'),
                    'message' => 'Absen masuk berhasil.',
                ]);
            }
        }

        if ($this->attendance->hasCheckedInToday($user)) {
            $this->fail('attendance', 'Anda sudah melakukan absensi hari ini.');
        }

        if ($message = $this->attendance->dayOffError($user)) {
            $this->fail('schedule', $message);
        }

        if ($message = $this->attendance->checkInWindowError($user, $moment)) {
            $this->fail('time', $message);
        }

        $latitude = (float) $request->validated('latitude');
        $longitude = (float) $request->validated('longitude');

        $office = $this->attendance->officeFor($user);

        if ($office === null) {
            $this->fail('office', 'Kantor Anda belum diatur. Hubungi admin.');
        }

        $distance = $this->attendance->distanceFrom($office, $latitude, $longitude);

        if ($message = $this->attendance->outOfRangeError($office, $distance)) {
            $this->fail('location', $message);
        }

        $imagePath = $this->storePhoto($request->file('photo'));

        if ($imagePath === null) {
            $this->fail('photo', 'Gagal menyimpan foto. Silakan coba lagi.');
        }

        $attendance = new Attendance([
            'user_id' => $user->id,
            'academic_year_id' => AcademicYear::getActive()?->id,
            'status' => $this->attendance->statusAt($user, $moment),
            'image_path' => $imagePath,
            'check_in_lat' => $latitude,
            'check_in_long' => $longitude,
            'distance_meters' => $distance,
            'client_uuid' => $clientUuid,
            'synced_at' => $capturedAt === null ? null : now(),
        ]);

        // Setting created_at before save keeps Eloquent from overwriting it:
        // updateTimestamps() only fills a created_at that is not already dirty.
        $attendance->created_at = $moment;
        $attendance->updated_at = $moment;
        $attendance->save();

        return response()->json([
            'status' => $attendance->status->apiValue(),
            'check_in_time' => $attendance->created_at?->format('H:i'),
            'message' => 'Absen masuk berhasil.',
        ], 201);
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Api/OfflineAttendanceTest.php`
Expected: PASS

Run: `php artisan test tests/Feature/Api`
Expected: PASS — kontrak API lama tetap utuh.

- [ ] **Step 5: Commit**

```bash
./vendor/bin/pint --dirty
git add app/Http/Controllers/Api/AttendanceController.php tests/Feature/Api/OfflineAttendanceTest.php
git commit -m "feat: accept queued check-ins at their capture time"
```

---

### Task 5: Absen pulang memakai `captured_at` + idempoten

**Files:**
- Modify: `app/Http/Controllers/Api/AttendanceController.php:92-142`
- Test: `tests/Feature/Api/OfflineAttendanceTest.php` (tambah test)

**Interfaces:**
- Consumes: `AttendanceService::checkOutWindowError($user, $at)` (Task 2); `StoreCheckoutRequest::capturedAt()`, `::clientUuid()` (Task 3).
- Produces: perilaku `POST /api/attendance/checkout` yang menerima kiriman antrean.

- [ ] **Step 1: Write the failing test**

Tambahkan ke `tests/Feature/Api/OfflineAttendanceTest.php`:

```php
test('a queued check-out is stored at its capture time and is idempotent', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 18:00:00'));
    $user = offlineTeacher();

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
    ]);
    $attendance->created_at = Carbon::parse('2026-08-03 07:00:00');
    $attendance->save();

    $payload = [
        'latitude' => -6.2,
        'longitude' => 106.8,
        // 15:35 — jendela absen pulang baru terbuka 15:30 (jadwal 16:00 dikurangi
        // before_check_out 30 menit). Sejak Task 2 jendela itu dinilai pada jam
        // tangkap, jadi jam tangkap harus berada di dalamnya.
        'captured_at' => '2026-08-03 15:35:00',
        'client_uuid' => '77777777-7777-4777-8777-777777777777',
    ];

    $first = $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', $payload);
    $second = $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', $payload);

    $first->assertStatus(200)->assertJsonPath('check_out_time', '15:35');
    $second->assertStatus(200)->assertJsonPath('check_out_time', '15:35');

    $fresh = $attendance->fresh();
    expect($fresh->check_out_at->format('H:i'))->toBe('15:35')
        ->and($fresh->check_out_synced_at->format('H:i'))->toBe('18:00');

    Carbon::setTestNow();
});

test('a queued check-out captured before the window opens is rejected', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-03 18:00:00'));
    $user = offlineTeacher();

    $attendance = Attendance::create([
        'user_id' => $user->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
    ]);
    $attendance->created_at = Carbon::parse('2026-08-03 07:00:00');
    $attendance->save();

    // Jam sinkron (18:00) sudah di dalam jendela, jam tangkap (15:00) belum.
    // Test ini yang menangkap regresi bila pemeriksaan jendela dikembalikan
    // ke now(): pada 18:00 bug-nya tak terlihat.
    $this->actingAs($user, 'sanctum')->postJson('/api/attendance/checkout', [
        'latitude' => -6.2,
        'longitude' => 106.8,
        'captured_at' => '2026-08-03 15:00:00',
        'client_uuid' => '88888888-8888-4888-8888-888888888888',
    ])->assertStatus(422)->assertJsonValidationErrors('time');

    expect($attendance->fresh()->check_out_at)->toBeNull();

    Carbon::setTestNow();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Api/OfflineAttendanceTest.php`
Expected: FAIL — `check_out_time` berisi `18:00`, dan kiriman kedua ditolak "sudah melakukan absensi pulang".

- [ ] **Step 3: Rewrite the checkout method**

Ganti method `checkout()` di `app/Http/Controllers/Api/AttendanceController.php`:

```php
    /**
     * Close out today's record. Scoped to the token's own user, so a teacher
     * can only ever check themselves out.
     */
    public function checkout(StoreCheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $capturedAt = $request->capturedAt();
        $clientUuid = $request->clientUuid();
        $moment = $capturedAt ?? now();

        $attendance = $this->attendance->todayFor($user);

        if ($attendance === null) {
            $this->fail('attendance', 'Anda belum melakukan absensi masuk hari ini.');
        }

        // Same replay guard as check-in: a retry after a lost response reports
        // the check-out that already landed.
        if ($clientUuid !== null && $attendance->check_out_client_uuid === $clientUuid) {
            return response()->json([
                'check_out_time' => $attendance->check_out_at?->format('H:i'),
                'message' => 'Absen pulang berhasil.',
            ]);
        }

        if ($attendance->check_out_at !== null) {
            $this->fail('attendance', 'Anda sudah melakukan absensi pulang hari ini.');
        }

        if ($message = $this->attendance->checkOutWindowError($user, $moment)) {
            $this->fail('time', $message);
        }

        $latitude = $request->validated('latitude');
        $longitude = $request->validated('longitude');
        $distance = null;

        // Coordinates are optional, but when sent they are held to the same
        // geofence as check-in.
        if ($latitude !== null && $longitude !== null) {
            $office = $this->attendance->officeFor($user);

            if ($office !== null) {
                $distance = $this->attendance->distanceFrom($office, (float) $latitude, (float) $longitude);

                if ($message = $this->attendance->outOfRangeError($office, $distance)) {
                    $this->fail('location', $message);
                }
            }
        }

        $attendance->update([
            'check_out_at' => $moment,
            'check_out_image_path' => $this->storePhoto($request->file('photo')),
            'check_out_lat' => $latitude,
            'check_out_long' => $longitude,
            'check_out_distance_meters' => $distance,
            'check_out_client_uuid' => $clientUuid,
            'check_out_synced_at' => $capturedAt === null ? null : now(),
        ]);

        return response()->json([
            'check_out_time' => $moment->format('H:i'),
            'message' => 'Absen pulang berhasil.',
        ]);
    }
```

- [ ] **Step 4: Run the full suite**

Run: `php artisan test`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
./vendor/bin/pint --dirty
git add app/Http/Controllers/Api/AttendanceController.php tests/Feature/Api/OfflineAttendanceTest.php
git commit -m "feat: accept queued check-outs at their capture time"
```

---

### Task 6: Badge "Offline" di panel admin

**Files:**
- Modify: `resources/views/admin/attendances/index.blade.php:75-80`
- Test: `tests/Feature/Admin/OfflineBadgeTest.php`

**Interfaces:**
- Consumes: kolom `synced_at` (Task 1).
- Produces: —

- [ ] **Step 1: Write the failing test**

Buat `tests/Feature/Admin/OfflineBadgeTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Attendance;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

function badgeAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::create([
        'name' => 'Admin Badge',
        'email' => 'badge'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $role->id,
    ]);
}

test('the attendance list marks rows that arrived from the offline queue', function () {
    $admin = badgeAdmin();
    $office = Office::create([
        'name' => 'MI Badge',
        'latitude' => -6.2,
        'longitude' => 106.8,
        'radius_meters' => 100,
    ]);
    $teacherRole = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $teacher = User::create([
        'name' => 'Guru Badge',
        'email' => 'guru'.uniqid().'@test.test',
        'password' => bcrypt('password'),
        'role_id' => $teacherRole->id,
        'office_id' => $office->id,
    ]);

    Attendance::create([
        'user_id' => $teacher->id,
        'status' => 'present',
        'image_path' => 'attendance/x.jpg',
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5.0,
        'synced_at' => Carbon::parse('2026-08-03 10:00:00'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.attendances.index'))
        ->assertOk()
        ->assertSee('Offline');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test tests/Feature/Admin/OfflineBadgeTest.php`
Expected: FAIL — teks "Offline" tak ditemukan.

- [ ] **Step 3: Add the badge**

Di `resources/views/admin/attendances/index.blade.php`, di dalam sel status, tepat setelah `</span>` penutup badge status:

```blade
                                    @if ($attendance->synced_at)
                                        {{-- Baris dari antrean offline: jam absen berasal dari
                                             perangkat, jam sinkron dari server. Ditandai agar
                                             sengketa bisa ditelusuri. --}}
                                        <span class="admin-chip ml-2 text-xs"
                                            title="Dikirim dari antrean offline pada {{ $attendance->synced_at->format('d M Y H:i') }}">
                                            Offline
                                        </span>
                                    @endif
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test tests/Feature/Admin/OfflineBadgeTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
./vendor/bin/pint --dirty
git add resources/views/admin/attendances/index.blade.php tests/Feature/Admin/OfflineBadgeTest.php
git commit -m "feat: mark offline-synced attendance in the admin list"
```

**Setelah Task 6: deploy backend.** Klien tidak dapat diuji end-to-end sebelum server menerima `captured_at`.

---

## FASE 2 — iOS

Berkas iOS berada di repo `frontend(ios)/` (tanpa remote; commit lokal saja). Semua path di bawah relatif terhadap folder itu.

### Task 7: Bedakan kegagalan transport di lapisan HTTP

**Files:**
- Modify: `AbsensiKu/AuthModels.swift:65-74`
- Modify: `AbsensiKu/APIClient.swift:72-78, 240-246`

**Interfaces:**
- Consumes: —
- Produces: `APIError.isTransportFailure: Bool` (default `false`).

- [ ] **Step 1: Add the flag to APIError**

Di `AbsensiKu/AuthModels.swift`, ganti struct `APIError`:

```swift
/// Error terstruktur dari API. Menyerap format error validasi Laravel
/// (`{ "message": ..., "errors": { field: [pesan] } }`).
struct APIError: Error, LocalizedError {
    let message: String
    var fieldErrors: [String: [String]]? = nil
    var statusCode: Int? = nil
    /// Permintaan tak pernah sampai ke server (tak ada koneksi, timeout).
    /// Hanya kegagalan seperti ini yang layak diantre — respons 422 berarti
    /// server sudah memutuskan, mengulangnya tak akan pernah berhasil.
    var isTransportFailure: Bool = false

    var errorDescription: String? { message }

    /// Apakah error karena sesi tidak valid / kedaluwarsa.
    var isUnauthorized: Bool { statusCode == 401 }
}
```

- [ ] **Step 2: Mark transport failures in APIClient**

Di `AbsensiKu/APIClient.swift`, pada method `request(_:method:body:authorized:)`, ganti blok catch jaringan:

```swift
        do {
            (data, response) = try await session.data(for: req)
        } catch {
            throw APIError(message: "Tidak dapat terhubung ke server. Periksa koneksi Anda.",
                           isTransportFailure: true)
        }
```

dan pada method `upload(...)` ganti blok yang sama:

```swift
        do {
            (data, response) = try await session.upload(for: req, from: body)
        } catch {
            throw APIError(message: "Tidak dapat terhubung ke server. Periksa koneksi Anda.",
                           isTransportFailure: true)
        }
```

- [ ] **Step 3: Verify the build**

Run: `cd "frontend(ios)" && DEVELOPER_DIR=/Applications/Xcode.app/Contents/Developer xcodebuild -project AbsensiKu.xcodeproj -scheme AbsensiKu -destination 'generic/platform=iOS Simulator' -configuration Debug build`
Expected: BUILD SUCCEEDED

- [ ] **Step 4: Commit**

```bash
git add AbsensiKu/AuthModels.swift AbsensiKu/APIClient.swift
git commit -m "feat: flag transport failures on APIError"
```

---

### Task 8: Kirim `captured_at` + `client_uuid` dari iOS

**Files:**
- Modify: `AbsensiKu/APIClient.swift:159-174`

**Interfaces:**
- Consumes: —
- Produces:
  - `APIClient.checkIn(photo:latitude:longitude:capturedAt:clientUuid:) async throws -> CheckInResponse`
  - `APIClient.checkOut(photo:latitude:longitude:capturedAt:clientUuid:) async throws -> CheckOutResponse`
  - `APIClient.iso8601` — formatter yang dipakai kedua pemanggil.

- [ ] **Step 1: Add the parameters**

Di `AbsensiKu/APIClient.swift`, ganti kedua convenience method:

```swift
    /// Format waktu tangkap yang diterima server (`date` di Laravel).
    static let capturedAtFormatter: ISO8601DateFormatter = {
        let f = ISO8601DateFormatter()
        f.formatOptions = [.withInternetDateTime]
        return f
    }()

    /// Absen masuk: unggah selfie + koordinat GPS (multipart/form-data).
    ///
    /// `capturedAt` & `clientUuid` hanya diisi untuk kiriman dari antrean
    /// offline; absen online biasa mengirimnya nil sehingga server memakai
    /// jamnya sendiri, persis seperti sebelum antrean ada.
    func checkIn(photo: Data, latitude: Double, longitude: Double,
                 capturedAt: Date? = nil, clientUuid: String? = nil) async throws -> CheckInResponse {
        var fields = ["latitude": "\(latitude)", "longitude": "\(longitude)"]
        if let capturedAt, let clientUuid {
            fields["captured_at"] = Self.capturedAtFormatter.string(from: capturedAt)
            fields["client_uuid"] = clientUuid
        }
        return try await upload("/api/attendance", fields: fields, photo: photo)
    }

    /// Absen pulang: selfie & koordinat opsional.
    func checkOut(photo: Data?, latitude: Double?, longitude: Double?,
                  capturedAt: Date? = nil, clientUuid: String? = nil) async throws -> CheckOutResponse {
        var fields: [String: String] = [:]
        if let latitude { fields["latitude"] = "\(latitude)" }
        if let longitude { fields["longitude"] = "\(longitude)" }
        if let capturedAt, let clientUuid {
            fields["captured_at"] = Self.capturedAtFormatter.string(from: capturedAt)
            fields["client_uuid"] = clientUuid
        }
        return try await upload("/api/attendance/checkout", fields: fields, photo: photo)
    }
```

- [ ] **Step 2: Verify the build**

Run: `cd "frontend(ios)" && DEVELOPER_DIR=/Applications/Xcode.app/Contents/Developer xcodebuild -project AbsensiKu.xcodeproj -scheme AbsensiKu -destination 'generic/platform=iOS Simulator' -configuration Debug build`
Expected: BUILD SUCCEEDED — parameter baru punya nilai bawaan, pemanggil lama tak berubah.

- [ ] **Step 3: Commit**

```bash
git add AbsensiKu/APIClient.swift
git commit -m "feat: send the capture time with attendance uploads"
```

---

### Task 9: Antrean absen di iOS

**Files:**
- Create: `AbsensiKu/AttendanceQueue.swift`

**Interfaces:**
- Consumes: —
- Produces:
  - `enum QueuedKind: String, Codable { case masuk, pulang }`
  - `struct QueuedAttendance: Codable, Identifiable` — properti `id: UUID`, `kind: QueuedKind`, `capturedAt: Date`, `latitude: Double`, `longitude: Double`, `photoFileName: String`
  - `final class AttendanceQueue` — `static let shared`, `func entries() -> [QueuedAttendance]`, `func enqueue(kind:capturedAt:latitude:longitude:photo:) -> QueuedAttendance?`, `func remove(_ entry: QueuedAttendance)`, `func photoData(for entry: QueuedAttendance) -> Data?`, `func has(kind: QueuedKind) -> Bool`

- [ ] **Step 1: Write the queue**

Buat `AbsensiKu/AttendanceQueue.swift`:

```swift
//
//  AttendanceQueue.swift
//  AbsensiKu
//
//  Antrean absen yang belum terkirim. Sinyal di sekolah sering hilang, dan
//  absen yang gagal terkirim tidak boleh ikut hilang bersamanya.
//
//  Isinya paling banyak dua entri per hari (masuk + pulang), jadi satu berkas
//  JSON + berkas JPEG sudah cukup — database lokal hanya menambah skema dan
//  migrasi yang tak terpakai.
//

import Foundation

enum QueuedKind: String, Codable {
    case masuk, pulang
}

struct QueuedAttendance: Codable, Identifiable {
    let id: UUID
    let kind: QueuedKind
    /// Jam saat foto dijepret — inilah yang dikirim sebagai `captured_at`.
    let capturedAt: Date
    let latitude: Double
    let longitude: Double
    let photoFileName: String
}

final class AttendanceQueue {
    static let shared = AttendanceQueue()

    private let fileManager = FileManager.default
    private let decoder = JSONDecoder()
    private let encoder = JSONEncoder()

    private init() {
        decoder.dateDecodingStrategy = .iso8601
        encoder.dateEncodingStrategy = .iso8601
    }

    private var directory: URL {
        let base = fileManager.urls(for: .applicationSupportDirectory, in: .userDomainMask)[0]
            .appendingPathComponent("AttendanceQueue", isDirectory: true)
        try? fileManager.createDirectory(at: base, withIntermediateDirectories: true)
        return base
    }

    private var indexURL: URL { directory.appendingPathComponent("queue.json") }

    /// Entri terlama dulu. Berkas rusak diperlakukan sebagai antrean kosong —
    /// gagal membaca antrean tidak boleh membuat aplikasi tak bisa dipakai.
    func entries() -> [QueuedAttendance] {
        guard let data = try? Data(contentsOf: indexURL),
              let list = try? decoder.decode([QueuedAttendance].self, from: data)
        else { return [] }
        return list.sorted { $0.capturedAt < $1.capturedAt }
    }

    func has(kind: QueuedKind) -> Bool {
        entries().contains { $0.kind == kind }
    }

    @discardableResult
    func enqueue(kind: QueuedKind, capturedAt: Date, latitude: Double,
                 longitude: Double, photo: Data) -> QueuedAttendance? {
        let entry = QueuedAttendance(
            id: UUID(),
            kind: kind,
            capturedAt: capturedAt,
            latitude: latitude,
            longitude: longitude,
            photoFileName: "\(UUID().uuidString).jpg"
        )
        do {
            try photo.write(to: directory.appendingPathComponent(entry.photoFileName))
            try save(entries() + [entry])
            return entry
        } catch {
            return nil
        }
    }

    func remove(_ entry: QueuedAttendance) {
        try? fileManager.removeItem(at: directory.appendingPathComponent(entry.photoFileName))
        try? save(entries().filter { $0.id != entry.id })
    }

    func photoData(for entry: QueuedAttendance) -> Data? {
        try? Data(contentsOf: directory.appendingPathComponent(entry.photoFileName))
    }

    private func save(_ list: [QueuedAttendance]) throws {
        try encoder.encode(list).write(to: indexURL, options: .atomic)
    }
}
```

- [ ] **Step 2: Verify the build**

Run: `cd "frontend(ios)" && DEVELOPER_DIR=/Applications/Xcode.app/Contents/Developer xcodebuild -project AbsensiKu.xcodeproj -scheme AbsensiKu -destination 'generic/platform=iOS Simulator' -configuration Debug build`
Expected: BUILD SUCCEEDED

- [ ] **Step 3: Commit**

```bash
git add AbsensiKu/AttendanceQueue.swift
git commit -m "feat: store unsent attendance on the device"
```

---

### Task 10: Pengirim antrean iOS + pemicunya

**Files:**
- Create: `AbsensiKu/AttendanceSync.swift`
- Modify: `AbsensiKu/DashboardStore.swift:39-83`

**Interfaces:**
- Consumes: `AttendanceQueue` (Task 9), `APIClient.checkIn/checkOut(...capturedAt:clientUuid:)` (Task 8), `APIError.isTransportFailure` (Task 7).
- Produces:
  - `@MainActor @Observable final class AttendanceSync` — `static let shared`, `var pendingCount: Int`, `var lastRejection: String?`, `func refreshPending()`, `func flush() async -> Bool` (true bila ada entri terkirim), `func startMonitoring(onFlushed: @escaping () async -> Void)`
  - `DashboardStore.queueOnFailure` — absen yang gagal transport masuk antrean.

- [ ] **Step 1: Write the sync engine**

Buat `AbsensiKu/AttendanceSync.swift`:

```swift
//
//  AttendanceSync.swift
//  AbsensiKu
//
//  Mengirim ulang antrean absen. Dipicu saat aplikasi kembali ke depan dan
//  saat jaringan pulih; guru cukup membuka app sekali di tempat bersinyal.
//

import Foundation
import Network
import Observation

@MainActor
@Observable
final class AttendanceSync {
    static let shared = AttendanceSync()

    /// Jumlah absen yang masih menunggu terkirim (dibaca layar Beranda).
    var pendingCount = 0
    /// Alasan penolakan terakhir dari server, ditampilkan sekali lalu dibuang.
    var lastRejection: String?

    private let monitor = NWPathMonitor()
    private var isFlushing = false
    private var isMonitoring = false

    private init() {
        pendingCount = AttendanceQueue.shared.entries().count
    }

    func refreshPending() {
        pendingCount = AttendanceQueue.shared.entries().count
    }

    /// Pantau jaringan sekali seumur hidup aplikasi.
    func startMonitoring(onFlushed: @escaping () async -> Void) {
        guard !isMonitoring else { return }
        isMonitoring = true
        monitor.pathUpdateHandler = { path in
            guard path.status == .satisfied else { return }
            Task { @MainActor in
                if await self.flush() { await onFlushed() }
            }
        }
        monitor.start(queue: DispatchQueue(label: "id.absensiku.netmonitor"))
    }

    /// Kirim entri terlama dulu. Mengembalikan true bila ada yang terkirim,
    /// supaya pemanggil dapat memuat ulang Beranda dari server.
    @discardableResult
    func flush() async -> Bool {
        guard !isFlushing else { return false }
        isFlushing = true
        defer {
            isFlushing = false
            refreshPending()
        }

        var didSend = false

        // Urutan terlama-dulu bukan kosmetik: absen pulang yang sampai sebelum
        // absen masuknya tersimpan akan ditolak server.
        for entry in AttendanceQueue.shared.entries() {
            guard let photo = AttendanceQueue.shared.photoData(for: entry) else {
                AttendanceQueue.shared.remove(entry)
                continue
            }
            do {
                switch entry.kind {
                case .masuk:
                    _ = try await APIClient.shared.checkIn(
                        photo: photo, latitude: entry.latitude, longitude: entry.longitude,
                        capturedAt: entry.capturedAt, clientUuid: entry.id.uuidString
                    )
                case .pulang:
                    _ = try await APIClient.shared.checkOut(
                        photo: photo, latitude: entry.latitude, longitude: entry.longitude,
                        capturedAt: entry.capturedAt, clientUuid: entry.id.uuidString
                    )
                }
                AttendanceQueue.shared.remove(entry)
                didSend = true
            } catch let error as APIError {
                if error.isTransportFailure {
                    // Masih tanpa sinyal — biarkan di antrean, coba lagi nanti.
                    break
                }
                if error.isUnauthorized { break }
                // Server sudah memutuskan; mengulangnya tak akan pernah
                // berhasil. Buang entrinya dan beri tahu alasannya sekali.
                lastRejection = error.message
                AttendanceQueue.shared.remove(entry)
            } catch {
                break
            }
        }

        return didSend
    }
}
```

- [ ] **Step 2: Queue instead of failing in DashboardStore**

Di `AbsensiKu/DashboardStore.swift`, ganti `checkIn`, `checkOut`, dan `submit`:

```swift
    /// Absen masuk dengan selfie + koordinat.
    @discardableResult
    func checkIn(image: UIImage, coordinate: CLLocationCoordinate2D) async -> Bool {
        guard let photo = image.jpegForUpload() else {
            errorMessage = "Foto tidak valid."
            return false
        }
        return await submit(kind: .masuk, photo: photo, coordinate: coordinate) {
            let res = try await APIClient.shared.checkIn(
                photo: photo, latitude: coordinate.latitude, longitude: coordinate.longitude
            )
            return res.message ?? "Absen masuk berhasil."
        }
    }

    /// Absen pulang dengan selfie + koordinat.
    @discardableResult
    func checkOut(image: UIImage, coordinate: CLLocationCoordinate2D) async -> Bool {
        guard let photo = image.jpegForUpload() else {
            errorMessage = "Foto tidak valid."
            return false
        }
        return await submit(kind: .pulang, photo: photo, coordinate: coordinate) {
            let res = try await APIClient.shared.checkOut(
                photo: photo, latitude: coordinate.latitude, longitude: coordinate.longitude
            )
            return res.message ?? "Absen pulang berhasil."
        }
    }

    /// Jalankan aksi absen, lalu resync data dari DB.
    ///
    /// Kegagalan transport tidak membuang absen: entrinya masuk antrean dan
    /// dikirim ulang saat sinyal kembali. Penolakan server (422) tetap
    /// ditampilkan sebagai error, karena mengulangnya tak akan berhasil.
    private func submit(kind: QueuedKind, photo: Data,
                        coordinate: CLLocationCoordinate2D,
                        _ action: () async throws -> String) async -> Bool {
        isSubmitting = true
        errorMessage = nil
        successMessage = nil
        defer { isSubmitting = false }
        do {
            successMessage = try await action()
            await load() // ambil ulang state otoritatif dari DB
            return true
        } catch let error as APIError {
            guard error.isTransportFailure else {
                errorMessage = error.message
                return false
            }
            let queued = AttendanceQueue.shared.enqueue(
                kind: kind, capturedAt: Date(),
                latitude: coordinate.latitude, longitude: coordinate.longitude,
                photo: photo
            )
            AttendanceSync.shared.refreshPending()
            if queued == nil {
                errorMessage = "Gagal menyimpan absen di perangkat."
                return false
            }
            successMessage = "Absen disimpan. Akan terkirim otomatis saat ada sinyal."
            return true
        } catch {
            errorMessage = "Terjadi kesalahan tak terduga."
            return false
        }
    }
```

- [ ] **Step 3: Verify the build**

Run: `cd "frontend(ios)" && DEVELOPER_DIR=/Applications/Xcode.app/Contents/Developer xcodebuild -project AbsensiKu.xcodeproj -scheme AbsensiKu -destination 'generic/platform=iOS Simulator' -configuration Debug build`
Expected: BUILD SUCCEEDED

- [ ] **Step 4: Commit**

```bash
git add AbsensiKu/AttendanceSync.swift AbsensiKu/DashboardStore.swift
git commit -m "feat: queue attendance when the network is unreachable"
```

---

### Task 11: Tampilan antrean di Beranda iOS

**Files:**
- Modify: `AbsensiKu/BerandaView.swift:40-110, 380-414`

**Interfaces:**
- Consumes: `AttendanceSync.shared` (Task 10), `AttendanceQueue.shared.has(kind:)` (Task 9).
- Produces: —

- [ ] **Step 1: Add the pending card and button gating**

Di `AbsensiKu/BerandaView.swift`, tambahkan properti pada `BerandaView`:

```swift
    @State private var sync = AttendanceSync.shared
```

Di dalam `VStack(spacing: 16)` pada ScrollView, sisipkan tepat sebelum `StatusBanner(...)`:

```swift
                            if sync.pendingCount > 0 { pendingCard }
```

Ganti pemanggilan `AttendanceActions` agar antrean ikut mengunci tombol:

```swift
                            AttendanceActions(
                                masukEnabled: !data.hasCheckedIn && !store.isSubmitting
                                    && !AttendanceQueue.shared.has(kind: .masuk),
                                pulangEnabled: data.hasCheckedIn && !data.hasCheckedOut
                                    && !store.isSubmitting
                                    && !AttendanceQueue.shared.has(kind: .pulang),
                                onMasuk: { start(.masuk) },
                                onPulang: { start(.pulang) }
                            )
```

Tambahkan kartu antrean sebagai properti `BerandaView`:

```swift
    /// Kartu antrean: status Beranda datang dari server, jadi tanpa penanda ini
    /// absen yang tertunda terlihat seolah belum pernah dilakukan.
    private var pendingCard: some View {
        HStack(spacing: 12) {
            Image(systemName: "arrow.triangle.2.circlepath")
                .font(.system(size: 16, weight: .semibold))
                .foregroundStyle(AppTheme.statLate)
            VStack(alignment: .leading, spacing: 2) {
                Text("\(sync.pendingCount) absen menunggu terkirim")
                    .font(.system(size: 13, weight: .bold))
                    .foregroundStyle(AppTheme.textMain)
                Text("Akan terkirim otomatis saat ada sinyal.")
                    .font(.system(size: 10))
                    .foregroundStyle(AppTheme.textMuted)
            }
            Spacer(minLength: 0)
            Button("Kirim sekarang") {
                Task {
                    if await sync.flush() { await store.load() }
                }
            }
            .font(.system(size: 11, weight: .bold))
            .buttonStyle(.plain)
            .foregroundStyle(AppTheme.accentDeep)
        }
        .padding(14)
        .glassEffect(.regular.tint(AppTheme.statLate.opacity(0.4)), in: .rect(cornerRadius: 16))
    }
```

- [ ] **Step 2: Flush on foreground and on network recovery**

Ganti modifier `.task` pada `body` dan tambahkan pemantauan:

```swift
        .task {
            if previewData == nil { await store.load() }
            sync.refreshPending()
            sync.startMonitoring { await store.load() }
            if await sync.flush() { await store.load() }
        }
```

Tambahkan penanganan kembali-ke-depan tepat setelah `.refreshable`:

```swift
        .onChange(of: scenePhase) { _, phase in
            guard phase == .active else { return }
            Task {
                if await sync.flush() { await store.load() }
            }
        }
```

dan properti environment-nya di `BerandaView`:

```swift
    @Environment(\.scenePhase) private var scenePhase
```

- [ ] **Step 3: Surface a rejected entry once**

Ganti computed property `toast` pada `BerandaView` sehingga penolakan antrean ikut tampil:

```swift
    @ViewBuilder private var toast: some View {
        if let message = store.errorMessage ?? sync.lastRejection ?? store.successMessage {
            let isError = store.errorMessage != nil || sync.lastRejection != nil
            HStack(spacing: 8) {
                Image(systemName: isError ? "exclamationmark.triangle.fill" : "checkmark.circle.fill")
                    .font(.system(size: 14, weight: .semibold))
                Text(message)
                    .font(.system(size: 12, weight: .semibold))
                Spacer(minLength: 0)
            }
            .foregroundStyle(isError ? AppTheme.statusLate : AppTheme.statusOk)
            .padding(14)
            .glassEffect(
                .regular.tint((isError ? AppTheme.statusLate : AppTheme.statusOk).opacity(0.5)),
                in: .rect(cornerRadius: 16)
            )
            .padding(.horizontal, 20)
            .padding(.bottom, 12)
            .transition(.move(edge: .bottom).combined(with: .opacity))
            .task(id: message) {
                try? await Task.sleep(for: .seconds(3))
                store.errorMessage = nil
                store.successMessage = nil
                sync.lastRejection = nil
            }
        }
    }
```

- [ ] **Step 4: Verify the build**

Run: `cd "frontend(ios)" && DEVELOPER_DIR=/Applications/Xcode.app/Contents/Developer xcodebuild -project AbsensiKu.xcodeproj -scheme AbsensiKu -destination 'generic/platform=iOS Simulator' -configuration Debug build`
Expected: BUILD SUCCEEDED

- [ ] **Step 5: Manual airplane-mode check**

1. Jalankan di iPhone fisik, login, aktifkan Mode Pesawat.
2. Absen masuk → sheet tertutup dengan "Absen disimpan. Akan terkirim otomatis saat ada sinyal."; kartu "1 absen menunggu terkirim" muncul; tombol Masuk nonaktif.
3. Matikan Mode Pesawat → kartu hilang dalam beberapa detik; Beranda menampilkan jam masuk **saat langkah 2 dilakukan**, bukan jam sinkron.

- [ ] **Step 6: Commit**

```bash
git add AbsensiKu/BerandaView.swift
git commit -m "feat: show queued attendance on the home screen"
```

---

## FASE 3 — ANDROID

Berkas Android berada di `frontend(android)/` (belum jadi repo git — commit hanya mungkin bila repo sudah diinisialisasi; kalau belum, lewati langkah commit dan laporkan). Build: `JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew --no-configuration-cache assembleDebug`.

### Task 12: Bedakan kegagalan transport di lapisan HTTP Android

**Files:**
- Modify: `app/src/main/java/ypdh/absensiku/data/api/Api.kt:16-23, 120-121`
- Test: `app/src/test/java/ypdh/absensiku/OfflineQueueTest.kt`

**Interfaces:**
- Consumes: —
- Produces: `ApiException.isTransportFailure: Boolean` (default `false`).

- [ ] **Step 1: Write the failing test**

Buat `app/src/test/java/ypdh/absensiku/OfflineQueueTest.kt`:

```kotlin
package ypdh.absensiku

import org.junit.Assert.assertFalse
import org.junit.Assert.assertTrue
import org.junit.Test
import ypdh.absensiku.data.api.ApiException
import ypdh.absensiku.data.api.MSG_OFFLINE
import ypdh.absensiku.data.api.apiCall
import kotlinx.coroutines.runBlocking
import java.io.IOException

class OfflineQueueTest {

    @Test
    fun `network failures are marked as transport failures`() = runBlocking {
        val error = runCatching {
            apiCall<Unit> { throw IOException("no route to host") }
        }.exceptionOrNull() as ApiException

        assertEquals(MSG_OFFLINE, error.message)
        assertTrue(error.isTransportFailure)
    }

    @Test
    fun `validation errors are not transport failures`() {
        val error = ApiException("Anda sudah melakukan absensi hari ini.", status = 422)
        assertFalse(error.isTransportFailure)
    }
}
```

Tambahkan import `org.junit.Assert.assertEquals` di berkas yang sama.

- [ ] **Step 2: Run test to verify it fails**

Run: `JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew --no-configuration-cache testDebugUnitTest --tests '*OfflineQueueTest*'`
Expected: FAIL — `isTransportFailure` tidak dikenal (gagal kompilasi).

- [ ] **Step 3: Add the flag**

Di `app/src/main/java/ypdh/absensiku/data/api/Api.kt`, ganti kelas `ApiException` dan blok catch `IOException`:

```kotlin
/** Error yang sudah dipetakan ke copy Bahasa Indonesia (rewrite.prd §3, §8). */
class ApiException(
    override val message: String,
    val fieldErrors: Map<String, List<String>> = emptyMap(),
    val status: Int = 0,
    /**
     * Permintaan tak pernah sampai ke server. Hanya kegagalan seperti ini yang
     * layak diantre — respons 422 berarti server sudah memutuskan.
     */
    val isTransportFailure: Boolean = false,
) : Exception(message) {
    /** Pesan field pertama, dipakai layar ganti password. */
    val firstFieldError: String? get() = fieldErrors.values.firstOrNull()?.firstOrNull()
}
```

```kotlin
} catch (e: IOException) {
    throw ApiException(MSG_OFFLINE, isTransportFailure = true)
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew --no-configuration-cache testDebugUnitTest --tests '*OfflineQueueTest*'`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/src/main/java/ypdh/absensiku/data/api/Api.kt app/src/test/java/ypdh/absensiku/OfflineQueueTest.kt
git commit -m "feat: flag transport failures on ApiException"
```

---

### Task 13: Kirim `captured_at` + `client_uuid` dari Android

**Files:**
- Modify: `app/src/main/java/ypdh/absensiku/data/api/ApiService.kt:28-42`

**Interfaces:**
- Consumes: —
- Produces: parameter `capturedAt: RequestBody?` dan `clientUuid: RequestBody?` pada `checkIn()` & `checkOut()`.

- [ ] **Step 1: Add the parts**

Di `app/src/main/java/ypdh/absensiku/data/api/ApiService.kt`, ganti kedua method:

```kotlin
    @Multipart
    @POST("api/attendance")
    suspend fun checkIn(
        @Part photo: MultipartBody.Part,
        @Part("latitude") latitude: RequestBody,
        @Part("longitude") longitude: RequestBody,
        // Hanya diisi oleh antrean offline; absen online mengirim null agar
        // server memakai jamnya sendiri.
        @Part("captured_at") capturedAt: RequestBody? = null,
        @Part("client_uuid") clientUuid: RequestBody? = null,
    ): AttendanceResponse

    @Multipart
    @POST("api/attendance/checkout")
    suspend fun checkOut(
        @Part photo: MultipartBody.Part?,
        @Part("latitude") latitude: RequestBody?,
        @Part("longitude") longitude: RequestBody?,
        @Part("captured_at") capturedAt: RequestBody? = null,
        @Part("client_uuid") clientUuid: RequestBody? = null,
    ): CheckoutResponse
```

- [ ] **Step 2: Verify the build**

Run: `JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew --no-configuration-cache assembleDebug`
Expected: BUILD SUCCESSFUL

- [ ] **Step 3: Commit**

```bash
git add app/src/main/java/ypdh/absensiku/data/api/ApiService.kt
git commit -m "feat: send the capture time with attendance uploads"
```

---

### Task 14: Antrean absen di Android

**Files:**
- Create: `app/src/main/java/ypdh/absensiku/data/AttendanceQueue.kt`
- Test: `app/src/test/java/ypdh/absensiku/OfflineQueueTest.kt` (tambah test)

**Interfaces:**
- Consumes: —
- Produces:
  - `enum class QueuedKind { MASUK, PULANG }`
  - `data class QueuedAttendance(val id: String, val kind: QueuedKind, val capturedAt: String, val latitude: Double, val longitude: Double, val photoFileName: String)`
  - `class AttendanceQueue(private val dir: File)` — `fun entries(): List<QueuedAttendance>`, `fun enqueue(...): QueuedAttendance?`, `fun remove(entry)`, `fun photo(entry): File?`, `fun has(kind): Boolean`
  - `fun decodeEntries(raw: String?): List<QueuedAttendance>` — fungsi murni, diuji langsung

- [ ] **Step 1: Write the failing test**

Tambahkan ke `app/src/test/java/ypdh/absensiku/OfflineQueueTest.kt`:

```kotlin
    @Test
    fun `a corrupt queue file reads as an empty queue instead of crashing`() {
        assertTrue(decodeEntries("{ not json").isEmpty())
        assertTrue(decodeEntries(null).isEmpty())
        assertTrue(decodeEntries("[]").isEmpty())
    }

    @Test
    fun `entries come back oldest first`() {
        val raw = """
            [
              {"id":"b","kind":"PULANG","capturedAt":"2026-08-03T15:00:00Z","latitude":-6.2,"longitude":106.8,"photoFileName":"b.jpg"},
              {"id":"a","kind":"MASUK","capturedAt":"2026-08-03T07:00:00Z","latitude":-6.2,"longitude":106.8,"photoFileName":"a.jpg"}
            ]
        """.trimIndent()

        assertEquals(listOf("a", "b"), decodeEntries(raw).map { it.id })
    }
```

Tambahkan import `ypdh.absensiku.data.decodeEntries`.

- [ ] **Step 2: Run test to verify it fails**

Run: `JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew --no-configuration-cache testDebugUnitTest --tests '*OfflineQueueTest*'`
Expected: FAIL — `decodeEntries` belum ada.

- [ ] **Step 3: Write the queue**

Buat `app/src/main/java/ypdh/absensiku/data/AttendanceQueue.kt`:

```kotlin
package ypdh.absensiku.data

import kotlinx.serialization.Serializable
import kotlinx.serialization.json.Json
import java.io.File
import java.util.UUID

/**
 * Antrean absen yang belum terkirim. Sinyal di sekolah sering hilang, dan absen
 * yang gagal terkirim tidak boleh ikut hilang bersamanya.
 *
 * ponytail: isinya paling banyak dua entri per hari (masuk + pulang), jadi satu
 * berkas JSON + berkas JPEG sudah cukup — Room hanya menambah skema dan migrasi
 * yang tak terpakai.
 */
enum class QueuedKind { MASUK, PULANG }

@Serializable
data class QueuedAttendance(
    val id: String,
    val kind: QueuedKind,
    /** ISO 8601 — dikirim apa adanya sebagai `captured_at`. */
    val capturedAt: String,
    val latitude: Double,
    val longitude: Double,
    val photoFileName: String,
)

private val queueJson = Json { ignoreUnknownKeys = true }

/**
 * Fungsi murni supaya bisa diuji tanpa sistem berkas. Isi rusak diperlakukan
 * sebagai antrean kosong: gagal membaca antrean tidak boleh membuat aplikasi
 * tak bisa dipakai.
 */
fun decodeEntries(raw: String?): List<QueuedAttendance> {
    if (raw.isNullOrBlank()) return emptyList()
    return runCatching { queueJson.decodeFromString<List<QueuedAttendance>>(raw) }
        .getOrDefault(emptyList())
        .sortedBy { it.capturedAt }
}

class AttendanceQueue(private val dir: File) {

    private val index: File get() = File(dir, "queue.json")

    init {
        dir.mkdirs()
    }

    fun entries(): List<QueuedAttendance> =
        decodeEntries(runCatching { index.readText() }.getOrNull())

    fun has(kind: QueuedKind): Boolean = entries().any { it.kind == kind }

    fun enqueue(
        kind: QueuedKind,
        capturedAt: String,
        latitude: Double,
        longitude: Double,
        photo: File,
    ): QueuedAttendance? = runCatching {
        val entry = QueuedAttendance(
            id = UUID.randomUUID().toString(),
            kind = kind,
            capturedAt = capturedAt,
            latitude = latitude,
            longitude = longitude,
            photoFileName = "${UUID.randomUUID()}.jpg",
        )
        photo.copyTo(File(dir, entry.photoFileName), overwrite = true)
        save(entries() + entry)
        entry
    }.getOrNull()

    fun remove(entry: QueuedAttendance) {
        File(dir, entry.photoFileName).delete()
        runCatching { save(entries().filterNot { it.id == entry.id }) }
    }

    fun photo(entry: QueuedAttendance): File? =
        File(dir, entry.photoFileName).takeIf { it.exists() }

    private fun save(list: List<QueuedAttendance>) {
        index.writeText(queueJson.encodeToString(list))
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew --no-configuration-cache testDebugUnitTest --tests '*OfflineQueueTest*'`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/src/main/java/ypdh/absensiku/data/AttendanceQueue.kt app/src/test/java/ypdh/absensiku/OfflineQueueTest.kt
git commit -m "feat: store unsent attendance on the device"
```

---

### Task 15: Pengirim antrean Android + tampilan Beranda

**Files:**
- Create: `app/src/main/java/ypdh/absensiku/data/AttendanceSync.kt`
- Modify: `app/src/main/java/ypdh/absensiku/AbsensiKuApp.kt:24-46` (objek `Deps` ada di berkas ini, bukan `Deps.kt`)
- Modify: `app/src/main/java/ypdh/absensiku/ui/home/DashboardScreen.kt:155-180`

**Interfaces:**
- Consumes: `AttendanceQueue` (Task 14), `ApiService.checkIn/checkOut(...capturedAt, clientUuid)` (Task 13), `ApiException.isTransportFailure` (Task 12).
- Produces: `AttendanceSync` — `val pending: StateFlow<Int>`, `suspend fun flush(): Boolean`, `fun startMonitoring(context: Context, onFlushed: suspend () -> Unit)`.

- [ ] **Step 1: Write the sync engine**

Buat `app/src/main/java/ypdh/absensiku/data/AttendanceSync.kt`:

```kotlin
package ypdh.absensiku.data

import android.content.Context
import android.net.ConnectivityManager
import android.net.Network
import android.net.NetworkRequest
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch
import kotlinx.coroutines.sync.Mutex
import kotlinx.coroutines.sync.withLock
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.asRequestBody
import okhttp3.RequestBody.Companion.toRequestBody
import ypdh.absensiku.data.api.ApiException
import ypdh.absensiku.data.api.ApiService

/**
 * Mengirim ulang antrean absen: saat app kembali ke depan dan saat jaringan
 * pulih. Guru cukup membuka app sekali di tempat bersinyal.
 */
class AttendanceSync(
    private val queue: AttendanceQueue,
    private val api: ApiService,
    private val scope: CoroutineScope,
) {
    private val _pending = MutableStateFlow(queue.entries().size)
    val pending: StateFlow<Int> = _pending

    private val _rejection = MutableStateFlow<String?>(null)
    val rejection: StateFlow<String?> = _rejection

    private val mutex = Mutex()
    private var monitoring = false

    fun refreshPending() {
        _pending.value = queue.entries().size
    }

    fun consumeRejection() {
        _rejection.value = null
    }

    fun startMonitoring(context: Context, onFlushed: suspend () -> Unit) {
        if (monitoring) return
        monitoring = true
        val manager = context.getSystemService(ConnectivityManager::class.java)
        manager.registerNetworkCallback(
            NetworkRequest.Builder().build(),
            object : ConnectivityManager.NetworkCallback() {
                override fun onAvailable(network: Network) {
                    scope.launch { if (flush()) onFlushed() }
                }
            },
        )
    }

    /**
     * Kirim entri terlama dulu. Urutan itu bukan kosmetik: absen pulang yang
     * sampai sebelum absen masuknya tersimpan akan ditolak server.
     */
    suspend fun flush(): Boolean = mutex.withLock {
        var didSend = false
        val text = "text/plain".toMediaType()

        for (entry in queue.entries()) {
            val photo = queue.photo(entry)
            if (photo == null) {
                queue.remove(entry)
                continue
            }
            val part = MultipartBody.Part.createFormData(
                "photo", "selfie.jpg", photo.asRequestBody("image/jpeg".toMediaType()),
            )
            try {
                val lat = entry.latitude.toString().toRequestBody(text)
                val lng = entry.longitude.toString().toRequestBody(text)
                val at = entry.capturedAt.toRequestBody(text)
                val uuid = entry.id.toRequestBody(text)
                when (entry.kind) {
                    QueuedKind.MASUK -> api.checkIn(part, lat, lng, at, uuid)
                    QueuedKind.PULANG -> api.checkOut(part, lat, lng, at, uuid)
                }
                queue.remove(entry)
                didSend = true
            } catch (e: ApiException) {
                if (e.isTransportFailure || e.status == 401) break
                // Server sudah memutuskan; mengulangnya tak akan berhasil.
                _rejection.value = e.message
                queue.remove(entry)
            } catch (e: Exception) {
                break
            }
        }

        refreshPending()
        didSend
    }
}
```

- [ ] **Step 2: Register in Deps**

Objek `Deps` berada di `app/src/main/java/ypdh/absensiku/AbsensiKuApp.kt`. Tambahkan dua properti setelah `location`:

```kotlin
    lateinit var location: LocationProvider
        private set
    lateinit var queue: AttendanceQueue
        private set
    lateinit var sync: AttendanceSync
        private set
```

dan dua baris di akhir `fun init(app: Application)`:

```kotlin
        location = LocationProvider(app)
        queue = AttendanceQueue(File(app.filesDir, "attendance-queue"))
        // Scope milik aplikasi: sinkronisasi harus selamat dari ViewModel yang
        // dibuang saat layar berganti.
        sync = AttendanceSync(queue, api, CoroutineScope(SupervisorJob() + Dispatchers.IO))
```

Import yang perlu ditambahkan di berkas itu:

```kotlin
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.SupervisorJob
import ypdh.absensiku.data.AttendanceQueue
import ypdh.absensiku.data.AttendanceSync
import java.io.File
```

- [ ] **Step 3: Queue on transport failure in the dashboard view model**

Di `app/src/main/java/ypdh/absensiku/ui/home/DashboardScreen.kt`, fungsi `submitAbsen(kind: AbsenKind, photo: File, latitude: Double, longitude: Double)` sudah memiliki keempat nilai yang dibutuhkan. Blok `finally { photo.delete() }` yang ada **dibiarkan** — `enqueue()` menyalin berkasnya lebih dulu, jadi menghapus berkas sementara tetap benar.

Ganti isi blok `catch (e: ApiException)`:

```kotlin
            } catch (e: ApiException) {
                if (!e.isTransportFailure) {
                    _state.update { it.copy(submitting = false, toast = toast(e.message, isError = true)) }
                    return@launch
                }
                // Tanpa sinyal: simpan, jangan buang absennya.
                val entry = Deps.queue.enqueue(
                    kind = if (kind == AbsenKind.MASUK) QueuedKind.MASUK else QueuedKind.PULANG,
                    capturedAt = java.time.Instant.now().toString(),
                    latitude = latitude,
                    longitude = longitude,
                    photo = photo,
                )
                Deps.sync.refreshPending()
                _state.update {
                    it.copy(
                        submitting = false,
                        toast = if (entry == null) {
                            toast("Gagal menyimpan absen di perangkat.", isError = true)
                        } else {
                            toast("Absen disimpan. Akan terkirim otomatis saat ada sinyal.")
                        },
                    )
                }
            }
```

- [ ] **Step 4: Show the pending card and gate the buttons**

Di `DashboardScreen`, kumpulkan `Deps.sync.pending` sebagai state dan tampilkan kartu di atas kartu status; nonaktifkan tombol Masuk/Pulang saat jenis yang sama sudah ada di antrean:

```kotlin
    val pending by Deps.sync.pending.collectAsState()

    LaunchedEffect(Unit) {
        Deps.sync.refreshPending()
        Deps.sync.startMonitoring(context) { viewModel.load(refresh = true) }
        if (Deps.sync.flush()) viewModel.load(refresh = true)
    }

    if (pending > 0) {
        GlassCard(Modifier.fillMaxWidth()) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Column(Modifier.weight(1f)) {
                    Text(
                        "$pending absen menunggu terkirim",
                        color = palette.textMain,
                        fontWeight = FontWeight.Bold,
                    )
                    Text(
                        "Akan terkirim otomatis saat ada sinyal.",
                        color = palette.textMuted,
                        fontSize = 12.sp,
                    )
                }
                TextButton(onClick = {
                    scope.launch { if (Deps.sync.flush()) viewModel.load(refresh = true) }
                }) { Text("Kirim sekarang") }
            }
        }
    }
```

Tombol absen: tambahkan `&& !Deps.queue.has(QueuedKind.MASUK)` pada syarat aktif tombol Masuk, dan `&& !Deps.queue.has(QueuedKind.PULANG)` pada tombol Pulang.

- [ ] **Step 5: Verify the build and the unit tests**

Run: `JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home" ./gradlew --no-configuration-cache assembleDebug testDebugUnitTest`
Expected: BUILD SUCCESSFUL, semua unit test PASS

- [ ] **Step 6: Manual airplane-mode check**

Sama seperti iOS Task 11 Step 5, dijalankan di perangkat Android.

- [ ] **Step 7: Commit**

```bash
git add app/src/main/java/ypdh/absensiku/data/AttendanceSync.kt app/src/main/java/ypdh/absensiku/Deps.kt app/src/main/java/ypdh/absensiku/ui/home/DashboardScreen.kt
git commit -m "feat: queue attendance when the network is unreachable"
```

---

## Catatan penutup

- **Deploy backend setelah Fase 1.** Tanpa itu klien mengirim `captured_at` ke server yang mengabaikannya, dan jam absen dari antrean akan salah tanpa peringatan apa pun.
- **iOS tak punya test target**; logika antrean iOS sengaja dibuat sepadan dengan versi Android yang teruji, dan diverifikasi lewat uji mode pesawat pada Task 11 Step 5.
- **Repo Android belum diinisialisasi.** Bila belum ada saat Fase 3 dijalankan, lewati langkah commit dan laporkan — jangan `git init` tanpa keputusan pemilik proyek.
