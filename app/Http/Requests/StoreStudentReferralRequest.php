<?php

namespace App\Http\Requests;

use App\Models\StudentReferral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', StudentReferral::class) ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:255'], 'observation' => ['required', 'string', 'max:10000'], 'observed_at' => ['required', 'date', 'before_or_equal:today'], 'urgency' => ['required', Rule::in(['normal', 'important', 'urgent'])], 'attachments' => ['sometimes', 'array', 'max:3'], 'attachments.*' => ['file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120']];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan rujukan wajib diisi.',
            'reason.string' => 'Alasan rujukan harus berupa teks.',
            'reason.max' => 'Alasan rujukan maksimal 255 karakter.',
            'observation.required' => 'Ringkasan pengamatan wajib diisi.',
            'observation.string' => 'Ringkasan pengamatan harus berupa teks.',
            'observation.max' => 'Ringkasan pengamatan maksimal 10.000 karakter.',
            'observed_at.required' => 'Tanggal pengamatan wajib diisi.',
            'observed_at.date' => 'Tanggal pengamatan tidak valid.',
            'observed_at.before_or_equal' => 'Tanggal pengamatan tidak boleh melebihi hari ini.',
            'urgency.required' => 'Urgensi wajib dipilih.',
            'urgency.in' => 'Urgensi tidak valid.',
            'attachments.array' => 'Lampiran harus berupa daftar berkas.',
            'attachments.max' => 'Lampiran maksimal tiga file.',
            'attachments.*.file' => 'Setiap lampiran harus berupa berkas.',
            'attachments.*.mimetypes' => 'Lampiran harus berupa JPG, PNG, atau PDF.',
            'attachments.*.max' => 'Ukuran setiap lampiran maksimal 5 MB.',
        ];
    }
}
