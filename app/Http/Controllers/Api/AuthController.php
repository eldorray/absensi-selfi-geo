<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Token authentication for the iOS client.
 *
 * Separate from Auth\LoginController, which keeps the Blade app on sessions.
 */
class AuthController extends Controller
{
    /**
     * Exchange credentials for a personal access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->with('office')
            ->where('email', $request->string('email')->value())
            ->first();

        // One message for both branches so the endpoint cannot be used to
        // discover which email addresses exist.
        if ($user === null || ! Hash::check($request->string('password')->value(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        return response()->json([
            'token' => $user->createToken('ios')->plainTextToken,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Revoke the token that made this request, leaving the user's other
     * devices signed in.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json(['message' => 'Berhasil keluar.']);
    }
}
