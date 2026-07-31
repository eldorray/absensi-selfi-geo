<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AccountSwitchLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Password-less switching between admin-linked non-admin accounts.
 *
 * Mirrors the Blade AccountSwitchController, but identity is carried by a
 * Sanctum token instead of the session: the requesting token is revoked and
 * a fresh token for the target account is issued.
 */
class AccountSwitchController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        /** @var User $current */
        $current = $request->user();
        $target = User::findOrFail((int) $validated['target_id']);

        // Server-authoritative guards, same rules as the web version.
        abort_if($current->isAdmin() || $target->isAdmin(), 403);
        abort_if($target->id === $current->id, 403);
        abort_unless($current->linkedAccounts()->whereKey($target->id)->exists(), 403);

        AccountSwitchLog::create([
            'from_user_id' => $current->id,
            'to_user_id' => $target->id,
            'ip_address' => $request->ip(),
        ]);

        $token = $request->user()?->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        $target->loadMissing('office');

        return response()->json([
            'token' => $target->createToken('ios')->plainTextToken,
            'user' => new UserResource($target),
        ]);
    }
}
