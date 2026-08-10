<?php

namespace App\Actions\Timetable;

use App\Enums\DayOfWeek;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Services\Timetable\TimetableConflictDetector;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpsertTimetableEntry
{
    public function __construct(private TimetableConflictDetector $conflictDetector) {}

    /**
     * Create or update a timetable slot after conflict and domain checks.
     *
     * @param  array{
     *     academic_year_id: int,
     *     school_class_id: int,
     *     day_of_week: int|string,
     *     period_number: int,
     *     subject_id: int,
     *     teacher_id: int
     * }  $data
     */
    public function handle(array $data, ?TimetableEntry $existing = null): TimetableEntry
    {
        $schoolClass = SchoolClass::query()->with(['subjects', 'grade'])->findOrFail($data['school_class_id']);
        $subject = Subject::query()->findOrFail($data['subject_id']);
        $teacher = Teacher::query()->findOrFail($data['teacher_id']);

        if ((int) $schoolClass->academic_year_id !== (int) $data['academic_year_id']) {
            throw ValidationException::withMessages([
                'school_class_id' => __('The class must belong to the selected academic year.'),
            ]);
        }

        if (! $schoolClass->subjects->contains('id', $subject->id)) {
            throw ValidationException::withMessages([
                'subject_id' => __('The subject must be linked to the selected class.'),
            ]);
        }

        if (! $subject->appliesToGrade($schoolClass->grade->number)) {
            throw ValidationException::withMessages([
                'subject_id' => __('The subject does not apply to this class grade.'),
            ]);
        }

        $day = DayOfWeek::from((int) $data['day_of_week']);
        $period = (int) $data['period_number'];

        if ($period < 1 || $period > TimetableEntry::MAX_PERIODS) {
            throw ValidationException::withMessages([
                'period_number' => __('Period must be between 1 and :max.', ['max' => TimetableEntry::MAX_PERIODS]),
            ]);
        }

        $conflicts = $this->conflictDetector->detect(
            (int) $data['academic_year_id'],
            (int) $data['school_class_id'],
            $day,
            $period,
            (int) $teacher->id,
            $existing?->id,
        );

        if ($conflicts['class_slot'] || $conflicts['teacher_slot']) {
            throw ValidationException::withMessages([
                'period_number' => $conflicts['messages'],
            ]);
        }

        return DB::transaction(function () use ($data, $day, $period, $existing): TimetableEntry {
            $payload = [
                'academic_year_id' => $data['academic_year_id'],
                'school_class_id' => $data['school_class_id'],
                'day_of_week' => $day,
                'period_number' => $period,
                'subject_id' => $data['subject_id'],
                'teacher_id' => $data['teacher_id'],
            ];

            if ($existing !== null) {
                $existing->update($payload);

                return $existing->refresh()->load(['subject', 'teacher.user', 'schoolClass']);
            }

            return TimetableEntry::query()->create($payload)->load(['subject', 'teacher.user', 'schoolClass']);
        });
    }
}
