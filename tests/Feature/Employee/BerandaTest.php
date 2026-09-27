<?php

// tests/Feature/Employee/BerandaTest.php

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\HomeroomAssignment;
use App\Models\Leave;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use Carbon\Carbon;
use Illuminate\Support\Str;

afterEach(fn () => Carbon::setTestNow());

function berandaTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id, 'name' => 'Siti Rahmawati']);
}

function berandaSchedule(User $user): void
{
    $year = AcademicYear::firstOrCreate(['name' => '2026/2027'], ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    WorkSchedule::create(['user_id' => $user->id, 'academic_year_id' => $year->id, 'day' => 'senin', 'check_in_time' => '07:00:00', 'check_out_time' => '14:00:00', 'is_active' => true]);
    WorkSetting::current()->update(['before_check_in' => 60, 'after_check_in' => 10, 'late_limit' => 120, 'before_check_out' => 30]);
}

function berandaCheckIn(User $user, string $at, AttendanceStatus $status = AttendanceStatus::Present): Attendance
{
    $attendance = Attendance::create(['user_id' => $user->id, 'status' => $status, 'image_path' => 'x.jpg', 'check_in_lat' => -6.2, 'check_in_long' => 106.8, 'distance_meters' => 38]);
    $attendance->created_at = Carbon::parse($at);
    $attendance->save();

    return $attendance;
}

test('beranda renders the header, date line and hero inside the guru shell', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 07:05:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSuccessful()
        ->assertSee('<body class="guru" data-teacher-ui="absenku-guru">', false)
        ->assertSee('data-profile-link="teacher-identity"', false)
        ->assertSee('aria-label="Buka profil Siti Rahmawati"', false)
        ->assertSee('Selamat pagi,')
        ->assertSee('Senin, 20 Juli')
        ->assertSee('data-hero="today"', false)
        ->assertSee('Tepat waktu s.d. 07.10')
        ->assertSee('href="'.route('attendance.selfie').'"', false)
        ->assertSee('Absen Masuk');
});

test('after check-in the hero waits for the check-out window', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 09:48:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);
    berandaCheckIn($teacher, '2026-07-20 06:52:00');

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('06.52')
        ->assertSee('aria-disabled="true"', false)
        ->assertSee('Pulang dibuka 13.30')
        ->assertSee('38 m dari titik absen')
        ->assertDontSee('href="'.route('attendance.checkout').'"', false);
});

test('the check-out window opens Absen Pulang', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 13:40:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);
    berandaCheckIn($teacher, '2026-07-20 06:52:00');

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('href="'.route('attendance.checkout').'"', false)
        ->assertSee('Absen Pulang');
});

test('a late check-in shows the late chip', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 09:00:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);
    berandaCheckIn($teacher, '2026-07-20 07:25:00', AttendanceStatus::Late);

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('g-chip g-chip--hero-late', false)
        ->assertSee('Terlambat');
});

test('a Sunday without a schedule shows Libur and no attendance action', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-19 08:00:00'));

    $this->actingAs(berandaTeacher())->get(route('attendance.dashboard'))
        ->assertSuccessful()
        ->assertSee('Libur')
        ->assertSee('Belum ada hari kerja bulan ini')
        ->assertDontSee('href="'.route('attendance.selfie').'"', false);
});

test('rekap shows recorded work days', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = berandaTeacher();
    berandaSchedule($teacher);
    berandaCheckIn($teacher, '2026-07-13 06:55:00');
    berandaCheckIn($teacher, '2026-07-20 06:55:00');

    // Hanya Senin terjadwal: 6, 13, 20 Juli = 3 hari kerja; 2 hadir tercatat.
    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('Rekap Juli')
        ->assertSee('2 dari 3 hari kerja tercatat');
});

test('pending leaves and unread referral notifications surface on beranda', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = berandaTeacher();
    Leave::create(['user_id' => $teacher->id, 'type' => 'sakit', 'start_date' => '2026-07-21', 'end_date' => '2026-07-21', 'reason' => 'Demam', 'status' => 'pending']);
    $teacher->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'App\Notifications\StudentReferralCreated', 'data' => ['ok' => true]]);

    $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('1 menunggu')
        ->assertSee('aria-label="Notifikasi, 1 belum dibaca"', false)
        ->assertSee('g-iconbtn__dot', false);
});

test('homeroom teachers see their class card and the Kelas tab', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = berandaTeacher();
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $class = SchoolClass::create(['school_level' => 'mi', 'name' => 'VIII-A', 'normalized_name' => SchoolClass::normalizeName('VIII-A'), 'grade_level' => 8, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    $html = $this->actingAs($teacher)->get(route('attendance.dashboard'))
        ->assertSee('Kelas wali · 2026/2027')
        ->assertSee('VIII-A')
        ->assertSee('Rujukan Saya')
        ->getContent();

    expect(substr_count($html, 'class="g-nav__item"'))->toBe(4);
});

test('a weekday without a schedule still offers Absen Masuk with the default hours', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 07:05:00'));

    $this->actingAs(berandaTeacher())->get(route('attendance.dashboard'))
        ->assertSee('href="'.route('attendance.selfie').'"', false)
        ->assertSee('16.00');
});

test('the focus ring on the green hero and install banner uses the amber accent', function () {
    expect(file_get_contents(resource_path('css/guru.css')))
        ->toMatch('/\.g-card--hero\s*,\s*\.g-install\s*\{\s*--g-focus:\s*#F2C879;/');
});

test('the install banner reserves room below the content while it is shown', function () {
    $html = $this->actingAs(berandaTeacher())->get(route('attendance.dashboard'))->getContent();

    expect($html)->toContain("document.body.classList.toggle('g-has-install', visible)")
        ->and(file_get_contents(resource_path('css/guru.css')))
        ->toMatch('/\.g-has-install\s+\.g-main\s*\{[^}]*padding-bottom:/');
});

test('the homeroom assignment is looked up once per request', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-20 10:00:00'));
    $teacher = berandaTeacher();
    $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
    $class = SchoolClass::create(['school_level' => 'mi', 'name' => 'VIII-B', 'normalized_name' => SchoolClass::normalizeName('VIII-B'), 'grade_level' => 8, 'is_active' => true]);
    HomeroomAssignment::create(['academic_year_id' => $year->id, 'school_class_id' => $class->id, 'teacher_id' => $teacher->id]);

    Illuminate\Support\Facades\DB::enableQueryLog();
    $this->actingAs($teacher)->get(route('attendance.dashboard'))->assertOk();
    $lookups = collect(Illuminate\Support\Facades\DB::getQueryLog())
        ->filter(fn (array $query) => str_contains($query['query'], 'from "homeroom_assignments"'))
        ->count();

    expect($lookups)->toBe(1);
});
