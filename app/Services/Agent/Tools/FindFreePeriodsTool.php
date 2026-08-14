<?php

namespace App\Services\Agent\Tools;

use App\Enums\DayOfWeek;
use App\Enums\PermissionName;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Timetable\PeriodSchedule;
use App\Services\Timetable\TimetableConflictDetector;

class FindFreePeriodsTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private TimetableConflictDetector $detector,
        private PeriodSchedule $periodSchedule,
    ) {}

    public function name(): string
    {
        return 'find_free_periods';
    }

    public function description(): string
    {
        return 'Find unscheduled periods (empty slots) in a class timetable. Optionally filter to one weekday.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'class_code' => $this->stringParam('Class code such as 10-A.'),
            'day_of_week' => $this->stringParam('Optional weekday name or number 1–5 (Monday–Friday).'),
        ], ['class_code']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ViewTimetable->value)
            || $user->can(PermissionName::ManageTimetable->value);
    }

    public function handle(User $user, array $arguments): array
    {
        $code = $this->stringArg($arguments, 'class_code');

        if ($code === null) {
            return ['ok' => false, 'error' => 'class_code is required.'];
        }

        $class = $this->scope->resolveClass($user, $code);
        $entries = $this->detector->entriesForClass($class->id, (int) $class->academic_year_id);
        $days = DayOfWeek::schoolDays();
        $dayFilter = $this->stringArg($arguments, 'day_of_week');

        if ($dayFilter !== null) {
            $days = [$this->scope->parseDay($dayFilter)];
        }

        $free = [];

        foreach ($days as $day) {
            foreach (range(1, TimetableEntry::MAX_PERIODS) as $period) {
                $occupied = $entries->contains(
                    fn (TimetableEntry $entry): bool => $this->scope->entryMatchesSlot($entry, $day, $period),
                );

                if ($occupied) {
                    continue;
                }

                $times = $this->periodSchedule->forPeriod($period);
                $free[] = [
                    'slot_key' => strtolower($day->name).'-'.$period,
                    'day' => $day->label(),
                    'day_of_week' => $day->value,
                    'period' => $period,
                    'time' => $times['label'],
                ];
            }
        }

        return [
            'ok' => true,
            'class_code' => $class->code,
            'class_id' => $class->id,
            'free_periods' => $free,
            'count' => count($free),
        ];
    }
}
