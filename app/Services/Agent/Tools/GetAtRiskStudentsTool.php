<?php

namespace App\Services\Agent\Tools;

use App\Enums\PermissionName;
use App\Models\User;
use App\Services\Agent\AgentScope;
use App\Services\Reporting\AttendanceAnalyticsReport;
use Illuminate\Support\Carbon;

class GetAtRiskStudentsTool extends AbstractAgentTool
{
    public function __construct(
        private AgentScope $scope,
        private AttendanceAnalyticsReport $attendanceAnalyticsReport,
    ) {}

    public function name(): string
    {
        return 'get_at_risk_students';
    }

    public function description(): string
    {
        return 'List students below the 80% monthly attendance threshold. Teachers are scoped to assigned classes.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'month' => $this->stringParam('Optional month as YYYY-MM. Defaults to the current month.'),
            'class_code' => $this->stringParam('Optional class code to narrow results.'),
        ]);
    }

    public function authorized(User $user): bool
    {
        return $this->scope->canViewAttendance($user) && $user->can(PermissionName::ViewReports->value);
    }

    public function handle(User $user, array $arguments): array
    {
        $month = $this->stringArg($arguments, 'month') ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)?->startOfMonth();

        if ($start === null) {
            return ['ok' => false, 'error' => 'month must be YYYY-MM.'];
        }

        $classIds = $this->scope->accessibleClassIds($user);
        $classCode = $this->stringArg($arguments, 'class_code');

        if ($classCode !== null) {
            $class = $this->scope->resolveClass($user, $classCode);
            $classIds = [$class->id];
        }

        $report = $this->attendanceAnalyticsReport->forMonth($start, $start->copy()->endOfMonth(), $classIds);

        return [
            'ok' => true,
            'month' => $start->format('Y-m'),
            'threshold' => AttendanceAnalyticsReport::AT_RISK_THRESHOLD,
            'at_risk' => array_slice($report['at_risk'], 0, 40),
            'count' => count($report['at_risk']),
        ];
    }
}
