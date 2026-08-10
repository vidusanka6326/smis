<?php

namespace App\Http\Requests\Admin;

use App\Enums\TeacherAssignmentRole;
use App\Models\Teacher;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SyncTeacherAssignmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Teacher $teacher */
        $teacher = $this->route('teacher');

        return $this->user()?->can('manageAssignments', $teacher) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.school_class_id' => ['required', 'integer', 'exists:classes,id'],
            'assignments.*.subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'assignments.*.role_in_assignment' => ['required', 'string', Rule::enum(TeacherAssignmentRole::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            foreach ($this->input('assignments', []) as $index => $assignment) {
                $role = TeacherAssignmentRole::tryFrom($assignment['role_in_assignment'] ?? '');

                if ($role?->requiresSubject() && blank($assignment['subject_id'] ?? null)) {
                    $validator->errors()->add(
                        "assignments.$index.subject_id",
                        __('A subject is required for subject teacher assignments.'),
                    );
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $assignments = collect($this->input('assignments', []))
            ->map(function (array $assignment): array {
                $assignment['subject_id'] = filled($assignment['subject_id'] ?? null)
                    ? $assignment['subject_id']
                    : null;

                return $assignment;
            })
            ->values()
            ->all();

        $this->merge([
            'assignments' => $assignments,
        ]);
    }
}
