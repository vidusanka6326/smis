<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\ReliefTeacherAssignment;
use App\Models\User;

class ReliefTeacherAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageTimetable->value)
            || $user->can(PermissionName::ViewTimetable->value);
    }

    public function view(User $user, ReliefTeacherAssignment $reliefTeacherAssignment): bool
    {
        if ($user->can(PermissionName::ManageTimetable->value)) {
            return true;
        }

        if (! $user->can(PermissionName::ViewTimetable->value)) {
            return false;
        }

        $entry = $reliefTeacherAssignment->timetableEntry;

        if ($user->isTeacher()) {
            return $user->teacher?->is($entry->teacher)
                || $user->teacher?->is($reliefTeacherAssignment->reliefTeacher);
        }

        if ($user->isStudent()) {
            return $user->student?->current_class_id === $entry->school_class_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function update(User $user, ReliefTeacherAssignment $reliefTeacherAssignment): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function delete(User $user, ReliefTeacherAssignment $reliefTeacherAssignment): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function restore(User $user, ReliefTeacherAssignment $reliefTeacherAssignment): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function forceDelete(User $user, ReliefTeacherAssignment $reliefTeacherAssignment): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }
}
