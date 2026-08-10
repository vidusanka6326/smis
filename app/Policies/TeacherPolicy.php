<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Teacher;
use App\Models\User;

class TeacherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function view(User $user, Teacher $teacher): bool
    {
        if ($user->can(PermissionName::ManageTeachers->value)) {
            return true;
        }

        return $user->isTeacher() && $user->teacher?->is($teacher);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function update(User $user, Teacher $teacher): bool
    {
        if ($user->can(PermissionName::ManageTeachers->value)) {
            return true;
        }

        return $user->isTeacher() && $user->teacher?->is($teacher);
    }

    public function delete(User $user, Teacher $teacher): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function restore(User $user, Teacher $teacher): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function forceDelete(User $user, Teacher $teacher): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function manageAssignments(User $user, Teacher $teacher): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }
}
