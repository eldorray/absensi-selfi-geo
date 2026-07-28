<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    /**
     * Ceiling for an uploaded attachment, in kilobytes.
     */
    public const MAX_ATTACHMENT_KB = 5120;

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
            'type' => ['required', 'in:izin,cuti,sakit'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimetypes:image/jpeg,image/png', 'max:'.self::MAX_ATTACHMENT_KB],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Jenis perizinan harus dipilih.',
            'type.in' => 'Jenis perizinan tidak valid.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'reason.required' => 'Alasan harus diisi.',
            'attachment.mimetypes' => 'Lampiran harus berupa gambar JPEG atau PNG.',
            'attachment.max' => 'Ukuran lampiran maksimal 5 MB.',
        ];
    }
}
