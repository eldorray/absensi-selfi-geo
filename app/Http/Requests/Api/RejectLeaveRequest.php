<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Alasan penolakan perizinan. Aturan dan copy-nya menyamai jalur web
 * (LeaveController@reject) agar penolakan yang sama tidak lolos di satu klien
 * lalu gagal di klien lain.
 */
class RejectLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Alasan penolakan harus diisi.',
            'rejection_reason.string' => 'Alasan penolakan harus berupa teks.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 500 karakter.',
        ];
    }
}
