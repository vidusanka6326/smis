<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\StudentAttendance;
use App\Models\User;

class StudentAttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewAttendance->value)
            || $user->can(PermissionName::ManageAttendance->value);
    }

    public function view(User $user, StudentAttendance $studentAttendance): bool
    {
        if ($user->isSchoolOffice() && $user->can(PermissionName::ViewAttendance->value)) {
            return true;
        }

        if ($user->isStudent() && $user->student?->is($studentAttendance->student)) {
            return $user->can(PermissionName::ViewAttendance->value);
        }

        if ($user->isTeacher() && $user->teacher) {
            $session = $studentAttendance->attendanceSession;

            return $user->can(PermissionName::ViewAttendance->value)
                && $user->teacher->canViewStudentAttendance($session->schoolClass, $session->subject_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageAttendance->value);
    }

    public function update(User $user, StudentAttendance $studentAttendance): bool
    {
        return $user->can('update', $studentAttendance->attendanceSession);
    }

    public function delete(User $user, StudentAttendance $studentAttendance): bool
    {
        return $user->can('update', $studentAttendance->attendanceSession);
    }
}
