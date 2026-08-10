<?php

namespace App\Http\Requests\Admin;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserStatus;
use App\Models\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateTeacherRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        /** @var Teacher $teacher */
        $teacher = $this->route('teacher');

        return $this->user()?->can('update', $teacher) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        /** @var Teacher $teacher */
        $teacher = $this->route('teacher');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
            'status' => ['required', 'string', Rule::enum(UserStatus::class)],
            'employee_no' => ['required', 'string', 'max:50', Rule::unique('teachers', 'employee_no')->ignore($teacher->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
