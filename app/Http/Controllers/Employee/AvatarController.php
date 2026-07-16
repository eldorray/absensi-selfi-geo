<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Streams user avatars through the app instead of relying on the
 * public/storage symlink (which some hosts do not serve reliably).
 */
class AvatarController extends Controller
{
    public function show(User $user): StreamedResponse|Response
    {
        $disk = Storage::disk('public');

        if (! $user->avatar_path || ! $disk->exists($user->avatar_path)) {
            throw new NotFoundHttpException;
        }

        return $disk->response(
            $user->avatar_path,
            headers: ['Cache-Control' => 'public, max-age=86400'],
        );
    }
}
