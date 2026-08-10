<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Mark;
use App\Models\User;

class MarkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewMarks->value)
            || $user->can(PermissionName::EnterMarks->value);
    }

    public function view(User $user, Mark $mark): bool
    {
        if ($user->isAdmin() && $user->can(PermissionName::ViewMarks->value)) {
            return true;
        }

        if ($user->isStudent() && $user->student?->is($mark->student)) {
            return $user->can(PermissionName::ViewMarks->value)
                && $mark->examSubject->exam->isPublished();
        }

        if ($user->isTeacher() && $user->can(PermissionName::ViewMarks->value)) {
            return $user->can('view', $mark->examSubject);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::EnterMarks->value);
    }

    public function update(User $user, Mark $mark): bool
    {
        return $user->can('enterMarks', $mark->examSubject);
    }

    public function delete(User $user, Mark $mark): bool
    {
        return $user->can('enterMarks', $mark->examSubject);
    }
}
