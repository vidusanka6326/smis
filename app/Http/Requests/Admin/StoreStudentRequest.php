<?php

namespace App\Http\Requests\Admin;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\Gender;
use App\Enums\UserStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStudentRequest extends FormRequest
{
    use PasswordValidationRules, ProfileValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('create', Student::class) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
            'admission_no' => ['required', 'string', 'max:50', 'unique:students,admission_no'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'string', Rule::enum(Gender::class)],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
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

            if (! $this->user()?->can('createInClass', [Student::class, $schoolClass])) {
                $validator->errors()->add('school_class_id', __('You may only create students in your own class.'));
            }
        });
    }
}
