<?php

namespace App\Services\Agent\Tools;

use App\Enums\DayOfWeek;
use App\Enums\PermissionName;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Timetable\PeriodSchedule;
use App\Services\Timetable\TimetableConflictDetector;

class GetTeacherTimetableTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private TimetableConflictDetector $detector,
        private PeriodSchedule $periodSchedule,
    ) {}

    public function name(): string
    {
        return 'get_teacher_timetable';
    }

    public function description(): string
    {
        return 'Return a teacher weekly timetable. Teachers may only view their own timetable unless they manage teachers.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'teacher_name' => $this->stringParam('Teacher name or employee number. Omit to use the signed-in teacher.'),
        ]);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ViewTimetable->value)
            || $user->can(PermissionName::ManageTimetable->value);
    }

    public function handle(User $user, array $arguments): array
    {
        $name = $this->stringArg($arguments, 'teacher_name');
        $yearId = $this->scope->requireAcademicYearId();

        if ($name === null) {
            if (! $user->isTeacher() || $user->teacher === null) {
                return ['ok' => false, 'error' => 'teacher_name is required.'];
            }

            $teacher = $user->teacher->load('user');
        } else {
            if (! $this->scope->canListTeachers($user) && ! ($user->isTeacher() && $user->teacher)) {
                return ['ok' => false, 'error' => 'You cannot look up other teachers.'];
            }

            $teacher = $this->scope->resolveTeacherOrFail($name);

            if (! $this->scope->canListTeachers($user) && ! $user->teacher?->is($teacher)) {
                return ['ok' => false, 'error' => 'You can only view your own timetable.'];
            }
        }

        $entries = $this->detector->entriesForTeacher($teacher->id, $yearId);
        $slots = [];

        foreach (DayOfWeek::schoolDays() as $day) {
            foreach (range(1, TimetableEntry::MAX_PERIODS) as $period) {
                $entry = $entries->first(
                    fn (TimetableEntry $item): bool => $this->scope->entryMatchesSlot($item, $day, $period),
                );

                if ($entry === null) {
                    continue;
                }

                $times = $this->periodSchedule->forPeriod($period);
                $slots[] = [
                    'day' => $day->label(),
                    'period' => $period,
                    'time' => $times['label'],
                    'class' => $entry->schoolClass?->code,
                    'subject' => $entry->subject?->name,
                ];
            }
        }

        return [
            'ok' => true,
            'teacher' => $teacher->user?->name,
            'employee_no' => $teacher->employee_no,
            'slots' => $slots,
        ];
    }
}
