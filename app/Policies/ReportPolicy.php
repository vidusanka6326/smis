<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewReports->value);
    }

    public function view(User $user, Report $report): bool
    {
        return $user->can(PermissionName::ViewReports->value);
    }

    /**
     * Student personal summary (not the school-wide analytics suite).
     */
    public function viewOwn(User $user): bool
    {
        return $user->isStudent()
            && $user->can(PermissionName::ViewMarks->value)
            && $user->student !== null;
    }
}
