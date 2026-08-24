<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionStudentReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('transition', $this->route('referral')) ?? false;
    }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['completed', 'rejected'])], 'safe_summary' => ['required', 'string', 'max:5000']];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status tujuan wajib dipilih.',
            'status.in' => 'Transisi status tidak valid.',
            'safe_summary.required' => 'Ringkasan aman wajib diisi.',
            'safe_summary.string' => 'Ringkasan aman harus berupa teks.',
            'safe_summary.max' => 'Ringkasan aman maksimal 5.000 karakter.',
        ];
    }
}
