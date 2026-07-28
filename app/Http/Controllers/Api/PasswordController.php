<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdatePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Ganti kata sandi milik sendiri untuk klien iOS.
 */
class PasswordController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function update(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini salah.',
            ]);
        }

        $new = (string) $request->validated('password');

        // 'password' di-cast 'hashed' dan 'visible_password' di-cast 'encrypted',
        // jadi cukup berikan nilai polos — model yang mengurus hashing/enkripsi.
        // visible_password ikut diperbarui agar tetap sinkron dengan web.
        $user->update([
            'password' => $new,
            'visible_password' => $new,
        ]);

        return response()->json(['message' => 'Kata sandi berhasil diperbarui.']);
    }
}
