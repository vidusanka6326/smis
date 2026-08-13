<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\TeacherAttendance;
use App\Models\User;

class TeacherAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewAttendance->value)
            || $user->can(PermissionName::ManageAttendance->value);
    }

    public function view(User $user, TeacherAttendance $teacherAttendance): bool
    {
        if ($user->isSchoolOffice() && $user->can(PermissionName::ViewAttendance->value)) {
            return true;
        }

        return $user->isTeacher()
            && $user->can(PermissionName::ViewAttendance->value)
            && $user->teacher?->is($teacherAttendance->teacher);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageAttendance->value);
    }

    public function update(User $user, TeacherAttendance $teacherAttendance): bool
    {
        if ($user->isSchoolOffice() && $user->can(PermissionName::ManageAttendance->value)) {
            return true;
        }

        return $user->isTeacher()
            && $user->can(PermissionName::ManageAttendance->value)
            && $user->teacher?->is($teacherAttendance->teacher);
    }

    public function delete(User $user, TeacherAttendance $teacherAttendance): bool
    {
        return $user->isSchoolOffice() && $user->can(PermissionName::ManageAttendance->value);
    }
}
