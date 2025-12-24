<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service for handling image upload and processing.
 * 
 * This service provides methods for saving base64 encoded images to storage.
 */
class ImageService
{
    /**
     * Decode Base64 image and save to storage.
     *
     * @param string $base64Image The Base64 encoded image string (with or without data URL prefix)
     * @param string $folder The folder name within storage (e.g., 'attendance', 'leaves')
     * @param int $userId The user ID for unique filename generation
     * @return string|null The relative path to the saved image, or null on failure
     */
    public function saveBase64Image(string $base64Image, string $folder, int $userId): ?string
    {
        try {
            // Remove data URL prefix if present (e.g., "data:image/jpeg;base64,")
            if (str_contains($base64Image, ',')) {
                $base64Image = explode(',', $base64Image)[1];
            }

            // Decode Base64
            $imageData = base64_decode($base64Image, true);

            if ($imageData === false) {
                return null;
            }

            // Generate unique filename
            $filename = sprintf(
                '%s/%d_%s_%s.jpg',
                $folder,
                $userId,
                now()->format('Y-m-d_H-i-s'),
                Str::random(8)
            );

            // Save to storage
            Storage::disk('public')->put($filename, $imageData);

            return $filename;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Delete an image from storage.
     *
     * @param string|null $path The path to the image
     * @return bool True if deleted, false otherwise
     */
    public function deleteImage(?string $path): bool
    {
        if (!$path) {
            return false;
        }

        return Storage::disk('public')->delete($path);
    }
}
