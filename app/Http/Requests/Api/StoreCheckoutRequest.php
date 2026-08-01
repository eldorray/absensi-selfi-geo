<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Http\Requests\Api\Concerns\AcceptsCapturedAt;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Check-out evidence is optional: the teacher is already on record for the day,
 * so a photo and coordinates are accepted but never demanded.
 */
class StoreCheckoutRequest extends FormRequest
{
    use AcceptsCapturedAt;

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
            'photo' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png', 'max:'.StoreAttendanceRequest::MAX_PHOTO_KB],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            ...$this->capturedAtRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.mimetypes' => 'Foto harus berupa gambar JPEG atau PNG.',
            'photo.max' => 'Ukuran foto maksimal 4 MB.',
            ...$this->capturedAtMessages(),
        ];
    }
}
