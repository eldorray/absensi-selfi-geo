<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\LeaveAdvanceNotice;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:izin,cuti,sakit'],
            'start_date' => ['required', 'date', 'after_or_equal:today', new LeaveAdvanceNotice($this->input('type'))],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis perizinan harus dipilih.',
            'start_date.required' => 'Tanggal mulai harus diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.required' => 'Tanggal selesai harus diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'reason.required' => 'Alasan harus diisi.',
            'attachment.image' => 'Lampiran harus berupa gambar.',
            'attachment.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }
}
