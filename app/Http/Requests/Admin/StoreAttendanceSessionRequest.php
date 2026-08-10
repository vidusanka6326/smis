<?php

namespace App\Http\Requests\Admin;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', AttendanceSession::class) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
            'taken_by_teacher_id' => ['nullable', 'integer', 'exists:teachers,id'],
            'finalize' => ['sometimes', 'boolean'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status' => ['required', 'string', Rule::enum(AttendanceStatus::class)],
        ];
    }
}
