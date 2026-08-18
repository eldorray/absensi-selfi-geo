<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class BrandingAssetController extends Controller
{
    public function show(string $type): Response
    {
        abort_unless(in_array($type, ['logo', 'icon'], true), 404);

        $settings = ApplicationSetting::current();
        $path = $type === 'logo' ? $settings->application_logo_path : $settings->application_icon_path;
        abort_if($path === null || ! Storage::disk('local')->exists($path), 404);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => Storage::disk('local')->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
