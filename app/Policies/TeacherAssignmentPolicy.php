<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\TeacherAssignment;
use App\Models\User;

class TeacherAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageTeachers->value) || $user->isTeacher();
    }

    public function view(User $user, TeacherAssignment $teacherAssignment): bool
    {
        if ($user->can(PermissionName::ManageTeachers->value)) {
            return true;
        }

        return $user->teacher?->is($teacherAssignment->teacher) ?? false;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function update(User $user, TeacherAssignment $teacherAssignment): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function delete(User $user, TeacherAssignment $teacherAssignment): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function restore(User $user, TeacherAssignment $teacherAssignment): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }

    public function forceDelete(User $user, TeacherAssignment $teacherAssignment): bool
    {
        return $user->can(PermissionName::ManageTeachers->value);
    }
}
