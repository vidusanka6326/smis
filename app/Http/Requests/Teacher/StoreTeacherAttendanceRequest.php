<?php

namespace App\Http\Requests\Teacher;

use App\Enums\AttendanceStatus;
use App\Models\TeacherAttendance;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null || ! $user->can('create', TeacherAttendance::class)) {
            return false;
        }

        $teacherId = (int) $this->input('teacher_id');

        return $user->teacher?->id === $teacherId;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::enum(AttendanceStatus::class)],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
