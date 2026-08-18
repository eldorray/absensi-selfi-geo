<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return ['name' => ['required', 'string', 'max:255'], 'normalized_name' => [Rule::unique('school_classes')->where('school_level', $this->route('schoolLevel'))->ignore($this->route('schoolClass'))], 'grade_level' => ['nullable', 'integer', 'between:1,9'], 'is_active' => ['boolean']];
    }
}
