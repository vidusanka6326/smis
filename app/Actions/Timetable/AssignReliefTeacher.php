<?php

namespace App\Actions\Timetable;

use App\Models\ReliefTeacherAssignment;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Timetable\TimetableConflictDetector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignReliefTeacher
{
    public function __construct(private TimetableConflictDetector $conflictDetector) {}

    /**
     * Manually assign a relief teacher for a specific date on a timetable entry.
     *
     * @param  array{relief_teacher_id: int, date: string, reason?: string|null}  $data
     */
    public function handle(TimetableEntry $entry, array $data, ?User $assignedBy = null): ReliefTeacherAssignment
    {
        $reliefTeacher = Teacher::query()->findOrFail($data['relief_teacher_id']);

        if ($reliefTeacher->is($entry->teacher)) {
            throw ValidationException::withMessages([
                'relief_teacher_id' => __('The relief teacher must be different from the scheduled teacher.'),
            ]);
        }

        $date = $data['date'];
        $carbon = Carbon::parse($date);

        if ((int) $carbon->dayOfWeekIso !== $entry->day_of_week->value) {
            throw ValidationException::withMessages([
                'date' => __('The relief date must fall on :day.', [
                    'day' => $entry->day_of_week->label(),
                ]),
            ]);
        }

        if ($this->conflictDetector->reliefTeacherHasConflict($entry, $reliefTeacher->id, $date)) {
            throw ValidationException::withMessages([
                'relief_teacher_id' => __('The relief teacher is already busy in that period on the selected date.'),
            ]);
        }

        return DB::transaction(function () use ($entry, $data, $reliefTeacher, $assignedBy): ReliefTeacherAssignment {
            return ReliefTeacherAssignment::query()->updateOrCreate(
                [
                    'timetable_entry_id' => $entry->id,
                    'date' => $data['date'],
                ],
                [
                    'relief_teacher_id' => $reliefTeacher->id,
                    'reason' => $data['reason'] ?? null,
                    'assigned_by' => $assignedBy?->id,
                ],
            )->load(['reliefTeacher.user', 'timetableEntry']);
        });
    }
}
