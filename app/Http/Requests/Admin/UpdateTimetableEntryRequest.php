<?php

namespace App\Http\Requests\Admin;

use App\Enums\DayOfWeek;
use App\Models\TimetableEntry;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTimetableEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var TimetableEntry $timetableEntry */
        $timetableEntry = $this->route('timetable_entry');

        return $this->user()?->can('update', $timetableEntry) ?? false;
    }

    /**
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'integer', 'exists:classes,id'],
            'day_of_week' => ['required', 'integer', Rule::enum(DayOfWeek::class)],
            'period_number' => ['required', 'integer', 'min:1', 'max:'.TimetableEntry::MAX_PERIODS],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
        ];
    }
}
