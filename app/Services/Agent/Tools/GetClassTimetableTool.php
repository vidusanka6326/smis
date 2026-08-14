<?php

namespace App\Services\Agent\Tools;

use App\Enums\DayOfWeek;
use App\Enums\PermissionName;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Timetable\PeriodSchedule;
use App\Services\Timetable\TimetableConflictDetector;

class GetClassTimetableTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private TimetableConflictDetector $detector,
        private PeriodSchedule $periodSchedule,
    ) {}

    public function name(): string
    {
        return 'get_class_timetable';
    }

    public function description(): string
    {
        return 'Return the weekly timetable for a class (Monday–Friday, periods 1–8).';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'class_code' => $this->stringParam('Class code such as 10-A.'),
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
        $slots = [];

        foreach (DayOfWeek::schoolDays() as $day) {
            foreach (range(1, TimetableEntry::MAX_PERIODS) as $period) {
                $entry = $entries->first(
                    fn (TimetableEntry $item): bool => $this->scope->entryMatchesSlot($item, $day, $period),
                );
                $times = $this->periodSchedule->forPeriod($period);
                $slots[] = [
                    'day' => $day->label(),
                    'day_of_week' => $day->value,
                    'period' => $period,
                    'time' => $times['label'],
                    'subject' => $entry?->subject?->name,
                    'teacher' => $entry?->teacher?->user?->name,
                    'occupied' => $entry !== null,
                ];
            }
        }

        return [
            'ok' => true,
            'class_code' => $class->code,
            'slots' => $slots,
        ];
    }
}
