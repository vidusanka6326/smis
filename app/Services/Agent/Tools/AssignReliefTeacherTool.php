<?php

namespace App\Services\Agent\Tools;

use App\Actions\Timetable\AssignReliefTeacher;
use App\Enums\ActivityAction;
use App\Enums\PermissionName;
use App\Models\ReliefTeacherAssignment;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;

class AssignReliefTeacherTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private AssignReliefTeacher $assignReliefTeacher,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'assign_relief_teacher';
    }

    public function description(): string
    {
        return 'Assign a relief teacher to cover an existing lesson on a specific date. The date must fall on the lesson weekday. Do not use this to fill an empty period.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'class_code' => $this->stringParam('Class code such as 10-A.'),
            'day_of_week' => $this->stringParam('Weekday of the original lesson if date is omitted.'),
            'period_number' => $this->integerParam('Period number from 1 to 8.'),
            'date' => $this->stringParam('Cover date as YYYY-MM-DD. Must match the lesson weekday.'),
            'teacher_name' => $this->stringParam('Relief teacher name or employee number.'),
            'reason' => $this->stringParam('Optional reason such as medical leave.'),
        ], ['class_code', 'period_number', 'date', 'teacher_name']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function handle(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('create', ReliefTeacherAssignment::class);

        $classCode = $this->stringArg($arguments, 'class_code');
        $period = $this->intArg($arguments, 'period_number');
        $date = $this->stringArg($arguments, 'date');
        $teacherName = $this->stringArg($arguments, 'teacher_name');
        $reason = $this->stringArg($arguments, 'reason');

        if ($classCode === null || $period === null || $date === null || $teacherName === null) {
            return ['ok' => false, 'error' => 'class_code, period_number, date, and teacher_name are required.'];
        }

        $class = $this->scope->resolveClass($user, $classCode);
        $teacher = $this->scope->resolveTeacherOrFail($teacherName);
        $carbon = Carbon::parse($date);
        $day = $this->scope->parseDay($carbon->dayOfWeekIso);

        $entry = TimetableEntry::query()
            ->with(['teacher.user', 'subject', 'schoolClass'])
            ->where('school_class_id', $class->id)
            ->where('academic_year_id', $class->academic_year_id)
            ->where('day_of_week', $day->value)
            ->where('period_number', $period)
            ->first();

        if ($entry === null) {
            return [
                'ok' => false,
                'error' => 'That period is empty, so relief cannot be assigned. Use assign_timetable_slot to fill a free period.',
            ];
        }

        $assignment = $this->assignReliefTeacher->handle($entry, [
            'relief_teacher_id' => $teacher->id,
            'date' => $carbon->toDateString(),
            'reason' => $reason,
        ], $user);

        $this->activityLogger->log(
            ActivityAction::AgentMutated,
            __('SMIS Agent assigned relief :teacher for :class :day period :period on :date.', [
                'teacher' => $teacher->user->name,
                'class' => $class->code,
                'day' => $day->label(),
                'period' => $period,
                'date' => $carbon->toDateString(),
            ]),
            $assignment,
            [
                'tool' => $this->name(),
                'relief_teacher_assignment_id' => $assignment->id,
            ],
            $user,
        );

        return [
            'ok' => true,
            'assigned' => true,
            'class' => $class->code,
            'date' => $carbon->toDateString(),
            'period' => $period,
            'original_teacher' => $entry->teacher?->user?->name,
            'relief_teacher' => $teacher->user?->name,
            'subject' => $entry->subject?->name,
        ];
    }
}
