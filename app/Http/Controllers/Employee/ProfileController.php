<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * ProfileController - Handle employee profile and password management.
 *
 * Follows Single Responsibility Principle by only handling profile-related operations.
 */
class ProfileController extends Controller
{
    /**
     * Display mobile profile page.
     */
    public function show(): View
    {
        return view('attendance.profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update user profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // Large originals are fine: they get downscaled/compressed on save.
            // The cap only guards server memory during GD processing.
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format gambar harus jpeg, jpg, png, atau webp.',
            'avatar.max' => 'Ukuran gambar maksimal 8MB.',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $stored = app(ImageService::class)->compressAndStore($file, 'avatars')
                ?? $file->store('avatars', 'public');

            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $validated['avatar_path'] = $stored;
        }

        unset($validated['avatar']);
        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Display password change page.
     */
    public function showPassword(): View
    {
        return view('attendance.password');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini harus diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
