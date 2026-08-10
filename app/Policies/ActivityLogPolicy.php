<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewActivityLog->value);
    }

    public function view(User $user, ActivityLog $activityLog): bool
    {
        return $user->can(PermissionName::ViewActivityLog->value);
    }
}
