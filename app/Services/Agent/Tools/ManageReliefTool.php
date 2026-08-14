<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\ReliefTeacherAssignment;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Audit\ActivityLogger;
use Illuminate\Support\Facades\Gate;

class ManageReliefTool extends AbstractAgentTool
{
    /**
     * @var list<string>
     */
    private const ACTIONS = ['list', 'delete'];

    public function __construct(
        private AgentScope $scope,
        private ActivityLogger $activityLogger,
    ) {}

    public function name(): string
    {
        return 'manage_relief';
    }

    public function description(): string
    {
        return 'List or delete relief-teacher covers. To assign a cover use assign_relief_teacher. Requires manage-timetable to delete.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'action' => $this->stringParam('list or delete.'),
            'class_code' => $this->stringParam('Optional class filter for list, or class of the cover to delete.'),
            'date' => $this->stringParam('Cover date YYYY-MM-DD.'),
            'period_number' => $this->integerParam('Period number when deleting a specific cover.'),
        ], ['action']);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ViewTimetable->value)
            || $user->can(PermissionName::ManageTimetable->value);
    }

    public function handle(User $user, array $arguments): array
    {
        return match ($this->normalizedAction($arguments)) {
            'list' => $this->list($user, $arguments),
            'delete' => $this->delete($user, $arguments),
            default => $this->unknownAction(self::ACTIONS),
        };
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function list(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('viewAny', ReliefTeacherAssignment::class);

        $query = ReliefTeacherAssignment::query()
            ->with(['reliefTeacher.user', 'timetableEntry.schoolClass', 'timetableEntry.subject', 'timetableEntry.teacher.user'])
            ->latest('date');

        $classCode = $this->stringArg($arguments, 'class_code');

        if ($classCode !== null) {
            $class = $this->scope->resolveClass($user, $classCode);
            $query->whereHas('timetableEntry', fn ($entry) => $entry->where('school_class_id', $class->id));
        }

        $date = $this->stringArg($arguments, 'date');

        if ($date !== null) {
            $query->whereDate('date', $date);
        }

        $rows = [];

        foreach ($query->limit(30)->get() as $assignment) {
            if (! $user->can('view', $assignment)) {
                continue;
            }

            $rows[] = [
                'id' => $assignment->id,
                'date' => $this->dateString($assignment->getRawOriginal('date')),
                'class' => $assignment->timetableEntry?->schoolClass?->code,
                'period' => $assignment->timetableEntry?->period_number,
                'subject' => $assignment->timetableEntry?->subject?->name,
                'original_teacher' => $assignment->timetableEntry?->teacher?->user?->name,
                'relief_teacher' => $assignment->reliefTeacher?->user?->name,
                'reason' => $assignment->reason,
            ];
        }

        return ['ok' => true, 'relief_assignments' => $rows];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function delete(User $user, array $arguments): array
    {
        $classCode = $this->stringArg($arguments, 'class_code');
        $date = $this->stringArg($arguments, 'date');
        $period = $this->intArg($arguments, 'period_number');

        if ($classCode === null || $date === null || $period === null) {
            return ['ok' => false, 'error' => 'class_code, date, and period_number are required to delete a cover.'];
        }

        $class = $this->scope->resolveClass($user, $classCode);

        $assignment = ReliefTeacherAssignment::query()
            ->with('timetableEntry')
            ->whereDate('date', $date)
            ->whereHas('timetableEntry', function ($query) use ($class, $period): void {
                $query->where('school_class_id', $class->id)
                    ->where('period_number', $period);
            })
            ->first();

        if ($assignment === null) {
            return ['ok' => false, 'error' => 'No relief assignment matched that class, date, and period.'];
        }

        Gate::forUser($user)->authorize('delete', $assignment);
        $assignment->delete();

        $this->logMutation(
            $this->activityLogger,
            $user,
            __('SMIS Agent removed relief for :class on :date period :period.', [
                'class' => $class->code,
                'date' => $date,
                'period' => $period,
            ]),
        );

        return ['ok' => true, 'deleted' => true];
    }
}
