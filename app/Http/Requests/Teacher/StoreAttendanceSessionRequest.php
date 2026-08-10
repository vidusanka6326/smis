<?php

namespace App\Http\Requests\Teacher;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $schoolClass = SchoolClass::query()->find($this->input('school_class_id'));

        if ($schoolClass === null) {
            return $this->user()?->can('create', AttendanceSession::class) ?? false;
        }

        $subjectId = $this->filled('subject_id') ? (int) $this->input('subject_id') : null;

        return $this->user()?->can('createForClass', [AttendanceSession::class, $schoolClass, $subjectId]) ?? false;
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
