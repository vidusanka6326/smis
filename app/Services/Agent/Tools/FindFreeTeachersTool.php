<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Timetable\PeriodSchedule;

class FindFreeTeachersTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private PeriodSchedule $periodSchedule,
    ) {}

    public function name(): string
    {
        return 'find_free_teachers';
    }

    public function description(): string
    {
        return 'List teachers who are not scheduled on a given weekday and period. Use after finding free class periods.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'day_of_week' => $this->stringParam('Weekday name or number 1–5 (Monday–Friday).'),
            'period_number' => $this->integerParam('Period number from 1 to 8.'),
        ], ['day_of_week', 'period_number']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageTeachers->value)
            || $user->can(PermissionName::ManageTimetable->value);
    }

    public function handle(User $user, array $arguments): array
    {
        $dayRaw = $this->stringArg($arguments, 'day_of_week') ?? $this->intArg($arguments, 'day_of_week');
        $period = $this->intArg($arguments, 'period_number');

        if ($dayRaw === null || $period === null) {
            return ['ok' => false, 'error' => 'day_of_week and period_number are required.'];
        }

        if ($period < 1 || $period > TimetableEntry::MAX_PERIODS) {
            return ['ok' => false, 'error' => 'period_number must be between 1 and 8.'];
        }

        $day = $this->scope->parseDay($dayRaw);
        $yearId = $this->scope->requireAcademicYearId();

        $busyIds = TimetableEntry::query()
            ->where('academic_year_id', $yearId)
            ->where('day_of_week', $day->value)
            ->where('period_number', $period)
            ->pluck('teacher_id')
            ->unique()
            ->all();

        $teachers = Teacher::query()
            ->with('user')
            ->whereNotIn('id', $busyIds)
            ->orderBy('employee_no')
            ->limit(25)
            ->get()
            ->map(fn (Teacher $teacher): array => [
                'id' => $teacher->id,
                'name' => $teacher->user->name,
                'employee_no' => $teacher->employee_no,
            ])
            ->all();

        $times = $this->periodSchedule->forPeriod($period);

        return [
            'ok' => true,
            'day' => $day->label(),
            'day_of_week' => $day->value,
            'period' => $period,
            'time' => $times['label'],
            'teachers' => $teachers,
            'count' => count($teachers),
        ];
    }
}
