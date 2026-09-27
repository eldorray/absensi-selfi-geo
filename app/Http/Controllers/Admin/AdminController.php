<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\View\View;

/**
 * AdminController - Handles admin dashboard with statistics.
 */
class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(AdminDashboardService $dashboard): View
    {
        return view('admin.dashboard', $dashboard->build(today()));
    }
}
