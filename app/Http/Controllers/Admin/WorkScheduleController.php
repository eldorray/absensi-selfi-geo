<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Office;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * WorkScheduleController - Manage work hours and schedules.
 */
class WorkScheduleController extends Controller
{
    /**
     * Display work schedules listing with tolerance settings.
     */
    public function index(Request $request): View
    {
        $settings = WorkSetting::current();

        $officeId = $request->input('office_id');
        $selectedOffice = $officeId ? Office::find((int) $officeId) : null;
        $activeYear = AcademicYear::getActive();

        // Get all non-admin users (users with roles where is_admin = false),
        // optionally scoped to a single office. Schedules are eager-loaded only
        // for the active academic year so the "X Hari" count reflects the year
        // being viewed. Not paginated: the list is filtered live (client-side)
        // by the search box, which needs every row present at once.
        $users = User::with([
            'workSchedules' => fn ($query) => $query->where('academic_year_id', $activeYear?->id),
            'role',
            'office',
        ])
            ->whereHas('role', function ($query) {
                $query->where('is_admin', false);
            })
            ->when($selectedOffice, fn ($query) => $query->where('office_id', $selectedOffice->id))
            ->orderBy('name')
            ->get();

        return view('admin.work-schedules.index', [
            'settings' => $settings,
            'users' => $users,
            'offices' => Office::orderBy('name')->get(),
            'selectedOffice' => $selectedOffice,
            'officeId' => $selectedOffice?->id,
            'activeYear' => $activeYear,
        ]);
    }

    /**
     * Update tolerance settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'before_check_in' => 'required|integer|min:0|max:360',
            'after_check_in' => 'required|integer|min:0|max:120',
            'late_limit' => 'required|integer|min:0|max:1440', // Up to 24 hours
            'before_check_out' => 'required|integer|min:0|max:360',
            'require_check_in' => 'boolean',
            'fine_tier1_amount' => 'required|integer|min:0|max:10000000',
            'fine_tier2_amount' => 'required|integer|min:0|max:10000000',
            'fine_tier1_max_minutes' => 'required|integer|min:1|max:1440',
        ]);

        $settings = WorkSetting::current();
        $settings->update([
            'before_check_in' => $validated['before_check_in'],
            'after_check_in' => $validated['after_check_in'],
            'late_limit' => $validated['late_limit'],
            'before_check_out' => $validated['before_check_out'],
            'require_check_in' => $request->boolean('require_check_in'),
            'fine_tier1_amount' => $validated['fine_tier1_amount'],
            'fine_tier2_amount' => $validated['fine_tier2_amount'],
            'fine_tier1_max_minutes' => $validated['fine_tier1_max_minutes'],
        ]);

        return back()->with('success', 'Pengaturan toleransi berhasil diperbarui.');
    }

    /**
     * Show form to edit user's work schedules for the active academic year.
     */
    public function edit(User $user): View|RedirectResponse
    {
        $activeYear = AcademicYear::getActive();

        if ($activeYear === null) {
            return redirect()
                ->route('admin.work-schedules.index', request()->query())
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu sebelum mengatur jam kerja.');
        }

        $schedules = $user->workSchedules()
            ->where('academic_year_id', $activeYear->id)
            ->get()
            ->keyBy('day');

        return view('admin.work-schedules.edit', [
            'user' => $user,
            'schedules' => $schedules,
            'days' => WorkSchedule::DAYS,
            'activeYear' => $activeYear,
            'previousYear' => $this->previousYearWithSchedules($user, $activeYear),
        ]);
    }

    /**
     * Update user's work schedules for the active academic year.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();

        if ($activeYear === null) {
            return redirect()
                ->route('admin.work-schedules.index', $request->query())
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu sebelum mengatur jam kerja.');
        }

        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.check_in_time' => 'required|date_format:H:i',
            'schedules.*.check_out_time' => 'required|date_format:H:i|after:schedules.*.check_in_time',
            'schedules.*.is_active' => 'boolean',
        ]);

        foreach ($validated['schedules'] as $day => $data) {
            WorkSchedule::updateOrCreate(
                ['user_id' => $user->id, 'day' => $day, 'academic_year_id' => $activeYear->id],
                [
                    'check_in_time' => $data['check_in_time'],
                    'check_out_time' => $data['check_out_time'],
                    'is_active' => isset($data['is_active']),
                ]
            );
        }

        return redirect()
            ->route('admin.work-schedules.index', $request->query())
            ->with('success', 'Jadwal kerja '.$user->name.' berhasil diperbarui.');
    }

    /**
     * Copy this user's schedules from the most recent prior academic year into
     * the active year, so the admin doesn't have to re-enter them each year.
     */
    public function copyFromPrevious(Request $request, User $user): RedirectResponse
    {
        $activeYear = AcademicYear::getActive();

        if ($activeYear === null) {
            return redirect()
                ->route('admin.work-schedules.index', $request->query())
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu sebelum mengatur jam kerja.');
        }

        $previousYear = $this->previousYearWithSchedules($user, $activeYear);

        if ($previousYear === null) {
            return back()->with('error', 'Tidak ada jadwal tahun sebelumnya untuk disalin.');
        }

        $sourceSchedules = WorkSchedule::where('user_id', $user->id)
            ->where('academic_year_id', $previousYear->id)
            ->get();

        foreach ($sourceSchedules as $source) {
            WorkSchedule::updateOrCreate(
                ['user_id' => $user->id, 'day' => $source->day, 'academic_year_id' => $activeYear->id],
                [
                    'check_in_time' => $source->check_in_time,
                    'check_out_time' => $source->check_out_time,
                    'is_active' => $source->is_active,
                ]
            );
        }

        return redirect()
            ->route('admin.work-schedules.edit', ['user' => $user] + $request->query())
            ->with('success', 'Jadwal disalin dari tahun ajaran '.$previousYear->name.'.');
    }

    /**
     * The most recent academic year before the active one that has any
     * schedules for this user, or null if there is none to copy from.
     */
    private function previousYearWithSchedules(User $user, AcademicYear $activeYear): ?AcademicYear
    {
        $yearIds = $user->workSchedules()
            ->whereNotNull('academic_year_id')
            ->distinct()
            ->pluck('academic_year_id');

        return AcademicYear::query()
            ->whereIn('id', $yearIds)
            ->where('start_date', '<', $activeYear->start_date)
            ->orderByDesc('start_date')
            ->first();
    }

    /**
     * Toggle schedule active status.
     */
    public function toggleStatus(WorkSchedule $schedule): RedirectResponse
    {
        $schedule->update(['is_active' => ! $schedule->is_active]);

        return back()->with('success', 'Status jadwal berhasil diubah.');
    }
}
