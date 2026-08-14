<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\TimetableEntry;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;

class DeleteTimetableSlotTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'delete_timetable_slot';
    }

    public function description(): string
    {
        return 'Remove a timetable lesson from a class period. Requires manage-timetable.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'class_code' => $this->stringParam('Class code such as 10-A.'),
            'day_of_week' => $this->stringParam('Weekday name or number 1–5.'),
            'period_number' => $this->integerParam('Period number from 1 to 8.'),
        ], ['class_code', 'day_of_week', 'period_number']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function handle(User $user, array $arguments): array
    {
        $classCode = $this->stringArg($arguments, 'class_code');
        $dayRaw = $this->stringArg($arguments, 'day_of_week') ?? $this->intArg($arguments, 'day_of_week');
        $period = $this->intArg($arguments, 'period_number');

        if ($classCode === null || $dayRaw === null || $period === null) {
            return ['ok' => false, 'error' => 'class_code, day_of_week, and period_number are required.'];
        }

        $class = $this->scope->resolveClass($user, $classCode);
        $day = $this->scope->parseDay($dayRaw);

        $entry = TimetableEntry::query()
            ->where('school_class_id', $class->id)
            ->where('academic_year_id', $class->academic_year_id)
            ->where('day_of_week', $day->value)
            ->where('period_number', $period)
            ->first();

        if ($entry === null) {
            return ['ok' => false, 'error' => 'That period is already empty.'];
        }

        Gate::forUser($user)->authorize('delete', $entry);

        $entry->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent deleted :class :day period :period.', [
                'class' => $class->code,
                'day' => $day->label(),
                'period' => $period,
            ]),
        );

        return [
            'ok' => true,
            'deleted' => true,
            'class' => $class->code,
            'day' => $day->label(),
            'period' => $period,
        ];
    }
}
