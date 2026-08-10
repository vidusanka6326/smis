<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageExaminations->value)
            || $user->can(PermissionName::EnterMarks->value)
            || $user->can(PermissionName::ViewMarks->value);
    }

    public function view(User $user, Exam $exam): bool
    {
        if ($user->can(PermissionName::ManageExaminations->value)) {
            return true;
        }

        if ($user->isStudent()) {
            return $user->can(PermissionName::ViewMarks->value) && $exam->isPublished();
        }

        return $user->can(PermissionName::ViewMarks->value)
            || $user->can(PermissionName::EnterMarks->value);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageExaminations->value);
    }

    public function update(User $user, Exam $exam): bool
    {
        return $user->can(PermissionName::ManageExaminations->value);
    }

    public function delete(User $user, Exam $exam): bool
    {
        return $user->can(PermissionName::ManageExaminations->value) && ! $exam->isPublished();
    }

    public function publish(User $user, Exam $exam): bool
    {
        return $user->can(PermissionName::ManageExaminations->value);
    }

    public function restore(User $user, Exam $exam): bool
    {
        return $user->can(PermissionName::ManageExaminations->value);
    }

    public function forceDelete(User $user, Exam $exam): bool
    {
        return $user->can(PermissionName::ManageExaminations->value);
    }
}
