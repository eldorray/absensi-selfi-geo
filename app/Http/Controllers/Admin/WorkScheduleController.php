<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    public function index(): View
    {
        $settings = WorkSetting::current();
        // Get all non-admin users (users with roles where is_admin = false)
        $users = User::with(['workSchedules', 'role'])
            ->whereHas('role', function ($query) {
                $query->where('is_admin', false);
            })
            ->orderBy('name')
            ->paginate(10);

        return view('admin.work-schedules.index', [
            'settings' => $settings,
            'users' => $users,
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
        ]);

        $settings = WorkSetting::current();
        $settings->update([
            'before_check_in' => $validated['before_check_in'],
            'after_check_in' => $validated['after_check_in'],
            'late_limit' => $validated['late_limit'],
            'before_check_out' => $validated['before_check_out'],
            'require_check_in' => $request->boolean('require_check_in'),
        ]);

        return back()->with('success', 'Pengaturan toleransi berhasil diperbarui.');
    }

    /**
     * Show form to edit user's work schedules.
     */
    public function edit(User $user): View
    {
        $schedules = $user->workSchedules()->get()->keyBy('day');
        $days = WorkSchedule::DAYS;

        return view('admin.work-schedules.edit', [
            'user' => $user,
            'schedules' => $schedules,
            'days' => $days,
        ]);
    }

    /**
     * Update user's work schedules.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.check_in_time' => 'required|date_format:H:i',
            'schedules.*.check_out_time' => 'required|date_format:H:i|after:schedules.*.check_in_time',
            'schedules.*.is_active' => 'boolean',
        ]);

        foreach ($validated['schedules'] as $day => $data) {
            WorkSchedule::updateOrCreate(
                ['user_id' => $user->id, 'day' => $day],
                [
                    'check_in_time' => $data['check_in_time'],
                    'check_out_time' => $data['check_out_time'],
                    'is_active' => isset($data['is_active']),
                ]
            );
        }

        return redirect()
            ->route('admin.work-schedules.index')
            ->with('success', 'Jadwal kerja ' . $user->name . ' berhasil diperbarui.');
    }

    /**
     * Toggle schedule active status.
     */
    public function toggleStatus(WorkSchedule $schedule): RedirectResponse
    {
        $schedule->update(['is_active' => !$schedule->is_active]);

        return back()->with('success', 'Status jadwal berhasil diubah.');
    }
}
