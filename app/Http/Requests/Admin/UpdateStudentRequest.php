<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $level = (string) $this->route('schoolLevel');

        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nisn' => ['nullable', 'string', 'max:20', 'required_without:nik', Rule::unique('students', 'nisn')->ignore($student)],
            'nik' => ['nullable', 'string', 'max:20', 'required_without:nisn', Rule::unique('students', 'nik')->ignore($student)],
            'school_class_id' => ['nullable', Rule::exists('school_classes', 'id')->where('school_level', $level)],
            'tempat_lahir' => ['nullable', 'string', 'max:100'], 'tanggal_lahir' => ['nullable', 'date'],
            'status' => ['required', Rule::in(Student::STATUSES)], 'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'alamat' => ['nullable', 'string'], 'no_telepon' => ['nullable', 'string', 'max:20'],
            'kebutuhan_khusus' => ['nullable', 'string', 'max:255'], 'disabilitas' => ['nullable', 'string', 'max:255'],
            'nomor_kip_pip' => ['nullable', 'string', 'max:50'], 'nama_ayah_kandung' => ['nullable', 'string', 'max:255'],
            'nama_ibu_kandung' => ['nullable', 'string', 'max:255'], 'nama_wali' => ['nullable', 'string', 'max:255'],
        ];
    }
}
