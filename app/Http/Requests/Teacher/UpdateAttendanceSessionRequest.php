<?php

namespace App\Http\Requests\Teacher;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var AttendanceSession $session */
        $session = $this->route('attendance_session');

        return $this->user()?->can('update', $session) ?? false;
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
            'finalize' => ['sometimes', 'boolean'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'records.*.status' => ['required', 'string', Rule::enum(AttendanceStatus::class)],
        ];
    }
}
