<?php

namespace App\Http\Requests\Admin;

use App\Concerns\PasswordValidationRules;
use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateStudentRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        /** @var Student $student */
        $student = $this->route('student');

        return $this->user()?->can('update', $student) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        /** @var Student $student */
        $student = $this->route('student');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->user_id)],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
            'admission_no' => ['required', 'string', 'max:50', Rule::unique('students', 'admission_no')->ignore($student->id)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'string', Rule::enum(Gender::class)],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'grama_niladari_division' => ['nullable', 'string', 'max:255'],
            'travel_method' => ['nullable', 'string', 'max:255'],
            'town' => ['nullable', 'string', 'max:255'],
            'relations_in_school' => ['nullable', 'array'],
            'relations_in_school.*' => ['exists:students,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'integer', 'exists:classes,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $schoolClass = SchoolClass::query()->find($this->input('school_class_id'));

            if ($schoolClass === null) {
                return;
            }

            if ((int) $schoolClass->academic_year_id !== (int) $this->input('academic_year_id')) {
                $validator->errors()->add('school_class_id', __('The class must belong to the selected academic year.'));
            }
        });
    }
}
