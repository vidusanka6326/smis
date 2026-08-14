<?php

namespace App\Services\Agent\Tools;

use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Attendance\AttendanceMonthlySummary;
use Illuminate\Support\Carbon;

class GetClassAttendanceTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private AttendanceMonthlySummary $attendanceMonthlySummary,
    ) {}

    public function name(): string
    {
        return 'get_class_attendance';
    }

    public function description(): string
    {
        return 'Monthly student attendance rollup for a class. Month defaults to the current month (YYYY-MM).';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'class_code' => $this->stringParam('Class code such as 10-A.'),
            'month' => $this->stringParam('Optional month as YYYY-MM.'),
        ], ['class_code']);
    }

    public function authorized(User $user): bool
    {
        return $this->scope->canViewAttendance($user);
    }

    public function handle(User $user, array $arguments): array
    {
        $classCode = $this->stringArg($arguments, 'class_code');

        if ($classCode === null) {
            return ['ok' => false, 'error' => 'class_code is required.'];
        }

        $class = $this->scope->resolveClass($user, $classCode);
        $month = $this->stringArg($arguments, 'month') ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)?->startOfMonth();

        if ($start === null) {
            return ['ok' => false, 'error' => 'month must be YYYY-MM.'];
        }

        $rows = $this->attendanceMonthlySummary->forClass($class->id, $start, $start->copy()->endOfMonth());

        return [
            'ok' => true,
            'class' => $class->code,
            'month' => $start->format('Y-m'),
            'students' => collect($rows)
                ->map(fn (array $row): array => [
                    'name' => $row['student']->user?->name,
                    'admission_no' => $row['student']->admission_no,
                    'percentage' => $row['percentage'],
                    'counts' => $row['counts'],
                ])
                ->take(40)
                ->values()
                ->all(),
        ];
    }
}
