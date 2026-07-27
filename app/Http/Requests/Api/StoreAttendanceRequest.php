<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Ceiling for an uploaded selfie, in kilobytes.
     */
    public const MAX_PHOTO_KB = 4096;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'mimetypes:image/jpeg,image/png', 'max:'.self::MAX_PHOTO_KB],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.required' => 'Foto selfie diperlukan.',
            'photo.mimetypes' => 'Foto harus berupa gambar JPEG atau PNG.',
            'photo.max' => 'Ukuran foto maksimal 4 MB.',
            'latitude.required' => 'Lokasi GPS diperlukan.',
            'longitude.required' => 'Lokasi GPS diperlukan.',
        ];
    }
}
