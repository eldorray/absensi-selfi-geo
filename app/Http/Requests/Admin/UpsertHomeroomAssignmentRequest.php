<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertHomeroomAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $assignment = $this->route('homeroomAssignment');

        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id', Rule::unique('homeroom_assignments')->where('academic_year_id', $this->integer('academic_year_id'))->ignore($assignment)],
            'teacher_id' => ['required', 'integer', 'exists:users,id', Rule::unique('homeroom_assignments')->where('academic_year_id', $this->integer('academic_year_id'))->ignore($assignment)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $class = SchoolClass::query()->find($this->integer('school_class_id'));
            $teacher = User::query()->with(['role', 'office'])->find($this->integer('teacher_id'));

            if (! $class || ! $teacher) {
                return;
            }

            if (! $class->is_active) {
                $validator->errors()->add('school_class_id', 'Kelas yang dipilih tidak aktif.');
            }

            if ($teacher->isAdmin() || $teacher->role?->slug !== 'guru') {
                $validator->errors()->add('teacher_id', 'Wali kelas harus memiliki role Guru.');
            }

            if ($teacher->office?->school_level !== $class->school_level) {
                $validator->errors()->add('teacher_id', 'Jenjang kantor guru harus sesuai dengan jenjang kelas.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'school_class_id.unique' => 'Kelas ini sudah memiliki wali kelas pada tahun ajaran tersebut.',
            'teacher_id.unique' => 'Guru ini sudah menjadi wali kelas pada tahun ajaran tersebut.',
        ];
    }
}
