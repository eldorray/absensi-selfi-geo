<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['normalized_name' => SchoolClass::normalizeName((string) $this->input('name'))]);
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'normalized_name' => [Rule::unique('school_classes')->where('school_level', $this->route('schoolLevel'))->ignore($this->route('schoolClass'))], 'grade_level' => ['nullable', 'integer', 'between:1,9'], 'is_active' => ['boolean'], 'teacher_id' => ['nullable', 'integer', 'exists:users,id']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->filled('teacher_id')) {
                return;
            }

            $year = AcademicYear::getActive();
            $teacher = User::query()->with(['role', 'office'])->find($this->integer('teacher_id'));
            $class = $this->route('schoolClass');

            if (! $year || ! $teacher || ! $class) {
                return;
            }

            if ($teacher->isAdmin() || $teacher->role?->slug !== 'guru') {
                $validator->errors()->add('teacher_id', 'Wali kelas harus memiliki role Guru.');
            }

            if ($teacher->office?->school_level !== $class->school_level) {
                $validator->errors()->add('teacher_id', 'Jenjang kantor guru harus sesuai dengan jenjang kelas.');
            }

            $currentAssignment = HomeroomAssignment::query()->where('academic_year_id', $year->id)->where('school_class_id', $class->id)->first();
            $teacherIsAssignedElsewhere = HomeroomAssignment::query()->where('academic_year_id', $year->id)->where('teacher_id', $teacher->id)->when($currentAssignment, fn ($query) => $query->whereKeyNot($currentAssignment->id))->exists();

            if ($teacherIsAssignedElsewhere) {
                $validator->errors()->add('teacher_id', 'Guru ini sudah menjadi wali kelas pada tahun ajaran aktif.');
            }
        }];
    }
}
