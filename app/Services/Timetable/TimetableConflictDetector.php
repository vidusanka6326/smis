<?php

namespace App\Services\Timetable;

use App\Enums\DayOfWeek;
use App\Models\ReliefTeacherAssignment;
use App\Models\TimetableEntry;
use Illuminate\Support\Collection;

class TimetableConflictDetector
{
    /**
     * Detect scheduling conflicts for a proposed timetable slot.
     *
     * @return array{class_slot: bool, teacher_slot: bool, messages: list<string>}
     */
    public function detect(
        int $academicYearId,
        int $schoolClassId,
        DayOfWeek|int $dayOfWeek,
        int $periodNumber,
        int $teacherId,
        ?int $ignoreEntryId = null,
    ): array {
        $day = $dayOfWeek instanceof DayOfWeek ? $dayOfWeek->value : $dayOfWeek;

        $classConflict = TimetableEntry::query()
            ->where('academic_year_id', $academicYearId)
            ->where('school_class_id', $schoolClassId)
            ->where('day_of_week', $day)
            ->where('period_number', $periodNumber)
            ->when($ignoreEntryId, fn ($q) => $q->whereKeyNot($ignoreEntryId))
            ->exists();

        $teacherConflict = TimetableEntry::query()
            ->where('academic_year_id', $academicYearId)
            ->where('teacher_id', $teacherId)
            ->where('day_of_week', $day)
            ->where('period_number', $periodNumber)
            ->when($ignoreEntryId, fn ($q) => $q->whereKeyNot($ignoreEntryId))
            ->exists();

        $messages = [];

        if ($classConflict) {
            $messages[] = __('This class already has a lesson in that period.');
        }

        if ($teacherConflict) {
            $messages[] = __('This teacher is already scheduled in another class for that period.');
        }

        return [
            'class_slot' => $classConflict,
            'teacher_slot' => $teacherConflict,
            'messages' => $messages,
        ];
    }

    public function hasConflicts(
        int $academicYearId,
        int $schoolClassId,
        DayOfWeek|int $dayOfWeek,
        int $periodNumber,
        int $teacherId,
        ?int $ignoreEntryId = null,
    ): bool {
        $result = $this->detect(
            $academicYearId,
            $schoolClassId,
            $dayOfWeek,
            $periodNumber,
            $teacherId,
            $ignoreEntryId,
        );

        return $result['class_slot'] || $result['teacher_slot'];
    }

    /**
     * Detect whether a relief teacher is already busy on the same weekday/period that day.
     *
     * Relief conflicts when the relief teacher has another timetable entry that day/period
     * (same weekday as the original entry) without themselves being relieved, or already
     * has another relief on that date/period.
     */
    public function reliefTeacherHasConflict(
        TimetableEntry $originalEntry,
        int $reliefTeacherId,
        string $date,
        ?int $ignoreReliefId = null,
    ): bool {
        $day = $originalEntry->day_of_week instanceof DayOfWeek
            ? $originalEntry->day_of_week->value
            : (int) $originalEntry->day_of_week;

        $busyOnRegularTimetable = TimetableEntry::query()
            ->where('academic_year_id', $originalEntry->academic_year_id)
            ->where('teacher_id', $reliefTeacherId)
            ->where('day_of_week', $day)
            ->where('period_number', $originalEntry->period_number)
            ->whereKeyNot($originalEntry->id)
            ->whereDoesntHave('reliefAssignments', function ($query) use ($date): void {
                $query->whereDate('date', $date);
            })
            ->exists();

        if ($busyOnRegularTimetable) {
            return true;
        }

        return ReliefTeacherAssignment::query()
            ->where('relief_teacher_id', $reliefTeacherId)
            ->whereDate('date', $date)
            ->when($ignoreReliefId, fn ($q) => $q->whereKeyNot($ignoreReliefId))
            ->whereHas('timetableEntry', function ($query) use ($originalEntry): void {
                $query->where('period_number', $originalEntry->period_number)
                    ->where('day_of_week', $originalEntry->day_of_week);
            })
            ->exists();
    }

    /**
     * @return Collection<int, TimetableEntry>
     */
    public function entriesForClass(int $schoolClassId, int $academicYearId): Collection
    {
        return TimetableEntry::query()
            ->with(['subject', 'teacher.user'])
            ->where('school_class_id', $schoolClassId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();
    }

    /**
     * @return Collection<int, TimetableEntry>
     */
    public function entriesForTeacher(int $teacherId, int $academicYearId): Collection
    {
        return TimetableEntry::query()
            ->with(['subject', 'schoolClass.grade', 'teacher.user'])
            ->where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->get();
    }
}
