<?php

namespace App\Services\Agent\Tools;

use App\Enums\ActivityAction;
use App\Enums\PermissionName;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class ListActivityLogsTool extends AbstractAgentTool
{
    public function name(): string
    {
        return 'list_activity_logs';
    }

    public function description(): string
    {
        return 'Search recent activity logs (who changed what). Requires view-activity-log.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([
            'search' => $this->stringParam('Optional name, email, or description fragment.'),
            'action' => $this->stringParam('Optional action key such as agent.mutated or marks.upserted.'),
        ]);
    }

    public function authorized(User $user): bool
    {
        return $user->can(PermissionName::ViewActivityLog->value);
    }

    public function handle(User $user, array $arguments): array
    {
        Gate::forUser($user)->authorize('viewAny', ActivityLog::class);

        $action = $this->stringArg($arguments, 'action');

        $logs = [];

        foreach (ActivityLog::query()
            ->with('causer')
            ->filter([
                'search' => $this->stringArg($arguments, 'search'),
                'action' => $action !== null && ActivityAction::tryFrom($action) !== null ? $action : null,
            ])
            ->latest('created_at')
            ->limit(20)
            ->get() as $log) {
            $logs[] = [
                'id' => $log->id,
                'action' => (string) $log->getRawOriginal('action'),
                'description' => $log->description,
                'causer' => $log->causer?->name,
                'created_at' => $this->datetimeString($log->getRawOriginal('created_at')),
            ];
        }

        return ['ok' => true, 'logs' => $logs];
    }
}
