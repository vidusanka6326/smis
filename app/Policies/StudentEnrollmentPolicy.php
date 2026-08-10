<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\StudentEnrollment;
use App\Models\User;

class StudentEnrollmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageStudents->value) || $user->isTeacher() || $user->isStudent();
    }

    public function view(User $user, StudentEnrollment $studentEnrollment): bool
    {
        if ($user->can(PermissionName::ManageStudents->value)) {
            return true;
        }

        if ($user->isStudent() && $user->student?->is($studentEnrollment->student)) {
            return true;
        }

        $teacher = $user->teacher;

        return $teacher !== null && $teacher->isClassTeacherOf($studentEnrollment->schoolClass);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageStudents->value);
    }

    public function update(User $user, StudentEnrollment $studentEnrollment): bool
    {
        return $user->can(PermissionName::ManageStudents->value);
    }

    public function delete(User $user, StudentEnrollment $studentEnrollment): bool
    {
        return $user->can(PermissionName::ManageStudents->value);
    }

    public function restore(User $user, StudentEnrollment $studentEnrollment): bool
    {
        return $user->can(PermissionName::ManageStudents->value);
    }

    public function forceDelete(User $user, StudentEnrollment $studentEnrollment): bool
    {
        return $user->can(PermissionName::ManageStudents->value);
    }
}
