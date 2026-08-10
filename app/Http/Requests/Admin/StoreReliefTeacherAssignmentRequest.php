<?php

namespace App\Http\Requests\Admin;

use App\Models\ReliefTeacherAssignment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReliefTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ReliefTeacherAssignment::class) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'timetable_entry_id' => ['required', 'integer', 'exists:timetables,id'],
            'relief_teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
