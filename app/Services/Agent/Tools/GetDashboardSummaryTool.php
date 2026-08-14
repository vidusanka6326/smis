<?php

namespace App\Services\Agent\Tools;

use App\Models\User;
use App\Services\Dashboard\RoleDashboardMetrics;

class GetDashboardSummaryTool extends AbstractAgentTool
{
    public function __construct(private RoleDashboardMetrics $metrics) {}

    public function name(): string
    {
        return 'get_dashboard_summary';
    }

    public function description(): string
    {
        return 'Return the signed-in user’s dashboard KPIs (counts, attendance average, at-risk count, latest exam stats).';
    }

    public function parameters(): array
    {
        return $this->objectSchema([]);
    }

    public function authorized(User $user): bool
    {
        return $user->isSchoolOffice() || $user->isTeacher();
    }

    public function handle(User $user, array $arguments): array
    {
        if ($user->isTeacher() && $user->teacher) {
            $payload = $this->metrics->forTeacher($user->teacher);

            return [
                'ok' => true,
                'role' => 'teacher',
                'stats' => $payload['stats'],
            ];
        }

        $payload = $this->metrics->forAdmin();

        return [
            'ok' => true,
            'role' => $user->isAdmin() ? 'admin' : 'officer',
            'stats' => $payload['stats'],
        ];
    }
}
