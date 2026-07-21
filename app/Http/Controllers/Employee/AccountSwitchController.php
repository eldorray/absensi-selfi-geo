<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AccountSwitchLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AccountSwitchController - Fast, password-less switching between
 * admin-linked non-admin accounts.
 */
class AccountSwitchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        /** @var User $current */
        $current = $request->user();
        $target = User::findOrFail((int) $validated['target_id']);

        // Server-authoritative guard (never trust the UI):
        // both parties must be non-admin, target must not be self, and the
        // target must be in the current account's linked set.
        abort_if($current->isAdmin() || $target->isAdmin(), 403);
        abort_if($target->id === $current->id, 403);
        abort_unless($current->linkedAccounts()->whereKey($target->id)->exists(), 403);

        AccountSwitchLog::create([
            'from_user_id' => $current->id,
            'to_user_id' => $target->id,
            'ip_address' => $request->ip(),
        ]);

        Auth::login($target);
        $request->session()->regenerate();

        return redirect()
            ->route('attendance.dashboard')
            ->with('success', "Berpindah ke akun {$target->name}.");
    }
}
