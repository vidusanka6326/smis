<?php

namespace App\Services\Agent\Tools;

use App\Models\User;

class ListCapabilitiesTool extends AbstractAgentTool
{
    public function name(): string
    {
        return 'list_capabilities';
    }

    public function description(): string
    {
        return 'List this user’s role, permissions, and which school operations they may perform through the agent. Call this when asked what you can do.';
    }

    public function parameters(): array
    {
        return $this->objectSchema([]);
    }

    public function authorized(User $user): bool
    {
        return true;
    }

    public function handle(User $user, array $arguments): array
    {
        $permissions = $user->getAllPermissions()->pluck('name')->values()->all();

        return [
            'ok' => true,
            'name' => $user->name,
            'roles' => $user->getRoleNames()->values()->all(),
            'permissions' => $permissions,
            'notes' => [
                'Tools already hide anything this user cannot do. Never bypass Policies.',
                'Teachers are scoped to assigned classes. Officers cannot manage officers.',
                'Empty timetable periods use assign_timetable_slot. Covering an existing lesson uses assign_relief_teacher.',
                'Creating people requires name, email, and a password. Gender is G or B.',
            ],
        ];
    }
}
