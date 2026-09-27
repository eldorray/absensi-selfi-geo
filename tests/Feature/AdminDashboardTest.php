<?php

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\AdminDashboardService;
use Illuminate\Support\Carbon;

function dashboardAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'administrator'], ['name' => 'Administrator', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id, 'name' => 'Siti Aminah']);
}

function dashboardTeacher(Office $office, string $name, bool $scheduled = true): User
{
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $user = User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id, 'name' => $name]);

    if ($scheduled) {
        WorkSchedule::create([
            'user_id' => $user->id,
            'day' => strtolower(now()->locale('id')->dayName),
            'check_in_time' => '07:00:00',
            'check_out_time' => '14:00:00',
            'is_active' => true,
        ]);
    }

    return $user;
}

function dashboardAttendance(User $user, AttendanceStatus $status, ?bool $liveness = true): Attendance
{
    return Attendance::create([
        'user_id' => $user->id,
        'status' => $status,
        'image_path' => 'x.jpg',
        'liveness_verified' => $liveness,
        'check_in_lat' => -6.2,
        'check_in_long' => 106.8,
        'distance_meters' => 5,
    ]);
}

beforeEach(function () {
    Carbon::setTestNow('2026-09-25 08:14:00'); // Jumat
    AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
});

afterEach(fn () => Carbon::setTestNow());

test('every employee lands in exactly one bucket and off-schedule staff are not counted', function () {
    $mi = Office::create(['name' => 'Kantor MI', 'school_level' => 'mi', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    $smp = Office::create(['name' => 'Kantor SMP', 'school_level' => 'smp', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);

    dashboardAttendance(dashboardTeacher($mi, 'Ahmad Tepat'), AttendanceStatus::Present);
    dashboardAttendance(dashboardTeacher($mi, 'Bayu Telat'), AttendanceStatus::Late, liveness: false);
    $izin = dashboardTeacher($smp, 'Citra Izin');
    Leave::create(['user_id' => $izin->id, 'type' => 'sakit', 'start_date' => '2026-09-25', 'end_date' => '2026-09-26', 'reason' => 'Demam', 'status' => 'approved']);
    dashboardTeacher($smp, 'Dimas Belum');
    dashboardTeacher($smp, 'Eko Libur', scheduled: false);
    Leave::create(['user_id' => $izin->id, 'type' => 'izin', 'start_date' => '2026-10-02', 'end_date' => '2026-10-02', 'reason' => 'KTP', 'status' => 'pending']);
    dashboardAdmin();

    $data = app(AdminDashboardService::class)->build(today());

    expect($data['totals'])->toBe([
        AdminDashboardService::ON_TIME => 1,
        AdminDashboardService::LATE => 1,
        AdminDashboardService::LEAVE => 1,
        AdminDashboardService::NOT_YET => 1,
        AdminDashboardService::OFF => 1,
    ])
        ->and($data['expected'])->toBe(4)
        ->and($data['present'])->toBe(2)
        ->and($data['presentPct'])->toBe(50)
        ->and($data['notYet']->pluck('name')->all())->toBe(['Dimas Belum'])
        ->and($data['pendingLeaves'])->toBe(1)
        ->and($data['pendingLeaveSummary'])->toBe('1 izin')
        ->and($data['manualPhotos'])->toBe(1)
        ->and($data['offices']->pluck('name')->all())->toBe(['Kantor MI', 'Kantor SMP'])
        ->and($data['offices'][1]['expected'])->toBe(2);
});

test('the dashboard renders the day, the queue, and the latest check-ins', function () {
    $mi = Office::create(['name' => 'Kantor MI', 'school_level' => 'mi', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    dashboardAttendance(dashboardTeacher($mi, 'Bayu Saputra'), AttendanceStatus::Late, liveness: false);
    dashboardTeacher($mi, 'Rina Oktaviani');

    $this->actingAs(dashboardAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-admin-ui="absenku"', false)
        ->assertSee('Selamat pagi, Siti')
        ->assertSee('Kehadiran hari ini')
        ->assertSee('Per kantor')
        ->assertSee('Perlu tindakan')
        ->assertSee('Rina Oktaviani')
        ->assertSee('Bayu Saputra')
        ->assertSee('Perlu dicek')
        ->assertSee('<title>Dashboard · '.config('app.name').'</title>', false)
        ->assertSee('admin-topbar-appearance', false)
        ->assertSee('@keydown.escape.window="open = false"', false);
});

test('the dashboard shows an empty day without dividing by zero', function () {
    $this->actingAs(dashboardAdmin())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Tidak ada jadwal kerja hari ini')
        ->assertSee('Belum ada absensi');
});

test('non-admin pages stay outside the admin scope', function () {
    $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);

    $this->actingAs(User::factory()->create(['role_id' => $role->id]))
        ->get(route('settings.profile.edit'))
        ->assertOk()
        ->assertDontSee('data-admin-ui="absenku"', false);

    $this->actingAs(User::factory()->create(['role_id' => $role->id]))
        ->get(route('admin.dashboard'))
        ->assertRedirect();
});

test('the daily report shows the real schedule times and prev/next day links', function () {
    $mi = Office::create(['name' => 'Kantor MI', 'school_level' => 'mi', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100]);
    dashboardTeacher($mi, 'Rina Oktaviani');

    $this->actingAs(dashboardAdmin())
        ->get(route('admin.reports.daily'))
        ->assertOk()
        ->assertSee('07:00–14:00')
        ->assertSee('Belum absen')
        ->assertSee('date=2026-09-24', false)
        ->assertSee('date=2026-09-26', false);
});
