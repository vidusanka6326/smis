<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoleName;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var SchoolClass $schoolClass */
        $schoolClass = $this->route('school_class');

        return $this->user()?->can('update', $schoolClass) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'grade_id' => ['required', 'integer', 'exists:grades,id'],
            'stream_id' => ['nullable', 'integer', 'exists:streams,id'],
            'class_teacher_id' => ['nullable', 'integer', 'exists:users,id'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $grade = Grade::query()->find($this->input('grade_id'));

            if ($grade === null) {
                return;
            }

            $streamId = $this->input('stream_id');

            if ($grade->allowsStream() && blank($streamId)) {
                $validator->errors()->add('stream_id', __('A stream is required for grades 12 and 13.'));
            }

            if (! $grade->allowsStream() && filled($streamId)) {
                $validator->errors()->add('stream_id', __('Streams may only be assigned to grades 12 and 13.'));
            }

            if ($this->filled('class_teacher_id')) {
                $teacher = User::query()->find($this->input('class_teacher_id'));

                if ($teacher === null || ! $teacher->hasRole(RoleName::Teacher)) {
                    $validator->errors()->add('class_teacher_id', __('The class teacher must be a user with the teacher role.'));
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'stream_id' => $this->filled('stream_id') ? $this->input('stream_id') : null,
            'class_teacher_id' => $this->filled('class_teacher_id') ? $this->input('class_teacher_id') : null,
            'subject_ids' => $this->input('subject_ids', []),
        ]);
    }
}
