<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public function edit(): View
    {
        return view('settings.branding', ['settings' => ApplicationSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_logo' => ['nullable', 'file', 'max:2048', 'mimetypes:image/png,image/webp,image/svg+xml'],
            'application_icon' => ['nullable', 'image', 'mimes:png', 'max:2048', 'dimensions:min_width=512,min_height=512,ratio=1/1'],
        ], [
            'application_logo.mimetypes' => 'Logo harus berupa PNG, WebP, atau SVG.',
            'application_icon.mimes' => 'Ikon aplikasi harus berupa PNG.',
            'application_icon.dimensions' => 'Ikon aplikasi harus persegi dan berukuran minimal 512 × 512 piksel.',
            '*.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $settings = ApplicationSetting::current();
        $updates = [];

        foreach (['application_logo', 'application_icon'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $pathField = $field.'_path';
            $newPath = $request->file($field)->store('branding', 'local');
            $oldPath = $settings->{$pathField};
            $updates[$pathField] = $newPath;

            if ($oldPath !== null) {
                Storage::disk('local')->delete($oldPath);
            }
        }

        if ($updates !== []) {
            $settings->update($updates);
        }

        return to_route('settings.branding.edit')->with('success', 'Branding aplikasi berhasil diperbarui.');
    }

    public function destroy(): RedirectResponse
    {
        $settings = ApplicationSetting::current();
        Storage::disk('local')->delete(array_filter([
            $settings->application_logo_path,
            $settings->application_icon_path,
        ]));
        $settings->update(['application_logo_path' => null, 'application_icon_path' => null]);

        return to_route('settings.branding.edit')->with('success', 'Branding aplikasi dikembalikan ke bawaan.');
    }
}
