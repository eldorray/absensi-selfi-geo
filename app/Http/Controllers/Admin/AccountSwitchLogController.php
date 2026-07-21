<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountSwitchLog;
use Illuminate\View\View;

/**
 * AccountSwitchLogController - Read-only audit view of account switches.
 */
class AccountSwitchLogController extends Controller
{
    public function index(): View
    {
        $logs = AccountSwitchLog::with(['fromUser', 'toUser'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.account-switches.index', ['logs' => $logs]);
    }
}
