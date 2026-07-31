<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateAvatarRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Replace the signed-in teacher's avatar. Same pipeline as the web:
     * downscale/compress, delete the old file, store under avatars/.
     */
    public function updateAvatar(UpdateAvatarRequest $request): JsonResponse
    {
        $user = $request->user();

        $file = $request->file('avatar');
        $stored = app(ImageService::class)->compressAndStore($file, 'avatars')
            ?? $file->store('avatars', 'public');

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->update(['avatar_path' => $stored]);
        $user->loadMissing('office');

        return response()->json(new UserResource($user));
    }
}
