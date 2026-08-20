<?php

namespace App\Http\Requests;

use App\Models\BkCategory;
use App\Models\BkRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBkRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BkRecord::class) ?? false;
    }

    public function rules(): array
    {
        $type = $this->input('record_type');
        $violation = $type === 'violation';
        $counseling = $type === 'counseling';

        return [
            'student_id' => ['required', Rule::exists('students', 'id')->where('school_level', $this->user()?->office?->school_level)],
            'related_student_ids' => ['sometimes', 'array'],
            'related_student_ids.*' => ['integer', 'distinct', 'different:student_id', Rule::exists('students', 'id')->where('school_level', $this->user()?->office?->school_level)],
            'record_type' => ['required', Rule::in(BkRecord::TYPES)],
            'category_id' => ['nullable', Rule::exists('bk_categories', 'id')->where('record_type', $type)->where('is_active', true)],
            'custom_topic' => ['nullable', 'required_without:category_id', 'string', 'max:255'],
            'occurred_at' => ['required', 'date'],
            'severity' => [$violation ? 'required' : 'nullable', Rule::in(BkRecord::SEVERITIES)],
            'chronology' => [$violation ? 'required' : 'nullable', 'string'],
            'action_taken' => [$violation ? 'required' : 'nullable', 'string'],
            'counseling_content' => [$counseling ? 'required' : 'nullable', 'string'],
            'counseling_result' => [$counseling ? 'required' : 'nullable', 'string'],
            'follow_up_plan' => ['nullable', 'string'],
            'next_follow_up_at' => ['nullable', 'date', 'after_or_equal:occurred_at'],
            'status' => ['sometimes', Rule::in(BkRecord::STATUSES)],
            'attachments' => ['sometimes', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimetypes:image/jpeg,image/png,application/pdf', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'student_id' => 'siswa utama',
            'related_student_ids' => 'siswa terkait',
            'related_student_ids.*' => 'siswa terkait',
            'record_type' => 'jenis catatan',
            'category_id' => 'kategori',
            'custom_topic' => 'topik lainnya',
            'occurred_at' => 'tanggal dan waktu kejadian',
            'severity' => 'tingkat pelanggaran',
            'chronology' => 'kronologi',
            'action_taken' => 'tindakan yang dilakukan',
            'counseling_content' => 'isi konseling',
            'counseling_result' => 'hasil konseling',
            'follow_up_plan' => 'rencana tindak lanjut',
            'next_follow_up_at' => 'jadwal tindak lanjut',
            'status' => 'status penyelesaian',
            'attachments' => 'lampiran',
            'attachments.*' => 'lampiran',
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'Siswa utama wajib dipilih.',
            'student_id.exists' => 'Siswa utama tidak tersedia pada unit Anda.',
            'related_student_ids.array' => 'Daftar siswa terkait tidak valid.',
            'related_student_ids.*.exists' => 'Siswa terkait tidak tersedia pada unit Anda.',
            'related_student_ids.*.distinct' => 'Siswa terkait tidak boleh dipilih lebih dari sekali.',
            'related_student_ids.*.different' => 'Siswa terkait tidak boleh sama dengan siswa utama.',
            'record_type.required' => 'Jenis catatan wajib dipilih.',
            'record_type.in' => 'Jenis catatan tidak valid.',
            'category_id.exists' => 'Kategori tidak aktif atau tidak sesuai dengan jenis catatan.',
            'custom_topic.required_without' => 'Topik lainnya wajib diisi jika kategori tidak dipilih.',
            'custom_topic.max' => 'Topik lainnya maksimal 255 karakter.',
            'occurred_at.required' => 'Tanggal dan waktu kejadian wajib diisi.',
            'occurred_at.date' => 'Tanggal dan waktu kejadian tidak valid.',
            'severity.required' => 'Tingkat pelanggaran wajib dipilih.',
            'severity.in' => 'Tingkat pelanggaran tidak valid.',
            'chronology.required' => 'Kronologi wajib diisi untuk catatan pelanggaran.',
            'action_taken.required' => 'Tindakan yang dilakukan wajib diisi untuk catatan pelanggaran.',
            'counseling_content.required' => 'Isi konseling wajib diisi untuk catatan konseling.',
            'counseling_result.required' => 'Hasil konseling wajib diisi untuk catatan konseling.',
            'next_follow_up_at.date' => 'Jadwal tindak lanjut tidak valid.',
            'next_follow_up_at.after_or_equal' => 'Jadwal tindak lanjut tidak boleh sebelum waktu kejadian.',
            'status.in' => 'Status penyelesaian tidak valid.',
            'attachments.array' => 'Daftar lampiran tidak valid.',
            'attachments.max' => 'Lampiran maksimal lima file.',
            'attachments.*.file' => 'Lampiran harus berupa file.',
            'attachments.*.mimetypes' => 'Lampiran harus berupa JPG, PNG, atau PDF.',
            'attachments.*.max' => 'Ukuran setiap lampiran maksimal 5 MB.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $categoryId = $this->input('category_id');
            if ($categoryId && ! BkCategory::query()->whereKey($categoryId)->where('record_type', $this->input('record_type'))->where('is_active', true)->exists()) {
                $validator->errors()->add('category_id', 'Kategori harus sesuai dengan jenis catatan dan masih aktif.');
            }
        }];
    }
}
