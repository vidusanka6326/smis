<?php

namespace App\Services\Agent\Tools;

use App\Actions\Timetable\UpsertTimetableEntry;
use App\Enums\ActivityAction;
use App\Enums\PermissionName;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;

class AssignTimetableSlotTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private UpsertTimetableEntry $upsertTimetableEntry,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'assign_timetable_slot';
    }

    public function description(): string
    {
        return 'Assign a teacher and subject to an empty class period (creates a timetable slot). Requires manage-timetable. Do not use this for covering an existing lesson — use assign_relief_teacher instead.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'class_code' => $this->stringParam('Class code such as 10-A.'),
            'day_of_week' => $this->stringParam('Weekday name or number 1–5.'),
            'period_number' => $this->integerParam('Period number from 1 to 8.'),
            'teacher_name' => $this->stringParam('Teacher full name or employee number.'),
            'subject_name' => $this->stringParam('Subject name or code linked to the class.'),
        ], ['class_code', 'day_of_week', 'period_number', 'teacher_name', 'subject_name']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function handle(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', TimetableEntry::class);

        $classCode = $this->stringArg($arguments, 'class_code');
        $dayRaw = $this->stringArg($arguments, 'day_of_week') ?? $this->intArg($arguments, 'day_of_week');
        $period = $this->intArg($arguments, 'period_number');
        $teacherName = $this->stringArg($arguments, 'teacher_name');
        $subjectName = $this->stringArg($arguments, 'subject_name');

        if ($classCode === null || $dayRaw === null || $period === null || $teacherName === null || $subjectName === null) {
            return ['ok' => false, 'error' => 'class_code, day_of_week, period_number, teacher_name, and subject_name are required.'];
        }

        $class = $this->scope->resolveClass($user, $classCode);
        $day = $this->scope->parseDay($dayRaw);
        $teacher = $this->scope->resolveTeacherOrFail($teacherName);
        $subject = $this->scope->resolveSubject($class, $subjectName);

        $entry = $this->upsertTimetableEntry->handle([
            'academic_year_id' => (int) $class->academic_year_id,
            'school_class_id' => $class->id,
            'day_of_week' => $day->value,
            'period_number' => $period,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->activityLogger->log(
            ActivityAction::AgentMutated,
            __('SMIS Agent assigned :teacher to :class :day period :period (:subject).', [
                'teacher' => $teacher->user->name,
                'class' => $class->code,
                'day' => $day->label(),
                'period' => $period,
                'subject' => $subject->name,
            ]),
            $entry,
            [
                'tool' => $this->name(),
                'timetable_entry_id' => $entry->id,
            ],
            $user,
        );

        return [
            'ok' => true,
            'assigned' => true,
            'class' => $class->code,
            'day' => $day->label(),
            'period' => $period,
            'teacher' => $teacher->user?->name,
            'subject' => $subject->name,
            'timetable_entry_id' => $entry->id,
        ];
    }
}
