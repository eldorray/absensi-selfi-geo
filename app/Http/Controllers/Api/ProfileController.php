<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * The signed-in teacher's own profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('office');

        return response()->json(new UserResource($user));
    }

    /**
     * Update the signed-in teacher's own profile.
     *
     * The user comes from the token, never from the payload, and only the
     * validated name and phone are written — an email, office_id or role_id in
     * the body is discarded.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->validated());
        $user->loadMissing('office');

        return response()->json(new UserResource($user));
    }
}
