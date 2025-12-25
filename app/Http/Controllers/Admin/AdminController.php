<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\User;
use Illuminate\View\View;

/**
 * AdminController - Handles admin dashboard with statistics.
 */
class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        // Get statistics
        $totalEmployees = User::role('employee')->count();
        $totalOffices = Office::count();
        $todayAttendances = Attendance::whereDate('created_at', today())->count();
        $todayLate = Attendance::whereDate('created_at', today())
            ->where('status', 'late')
            ->count();

        // Recent attendances
        $recentAttendances = Attendance::with(['user', 'user.office'])
            ->latest()
            ->take(10)
            ->get();

        // Attendance by office today
        $attendanceByOffice = Office::withCount([
            'users as attendance_count' => function ($query) {
                $query->whereHas('attendances', function ($q) {
                    $q->whereDate('created_at', today());
                });
            },
        ])->get();

        return view('admin.dashboard', [
            'totalEmployees' => $totalEmployees,
            'totalOffices' => $totalOffices,
            'todayAttendances' => $todayAttendances,
            'todayLate' => $todayLate,
            'recentAttendances' => $recentAttendances,
            'attendanceByOffice' => $attendanceByOffice,
        ]);
    }
}
