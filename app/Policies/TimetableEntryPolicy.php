<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\TimetableEntry;
use App\Models\User;

class TimetableEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageTimetable->value)
            || $user->can(PermissionName::ViewTimetable->value);
    }

    public function view(User $user, TimetableEntry $timetableEntry): bool
    {
        if ($user->can(PermissionName::ManageTimetable->value)) {
            return true;
        }

        if (! $user->can(PermissionName::ViewTimetable->value)) {
            return false;
        }

        if ($user->isTeacher() && $user->teacher?->is($timetableEntry->teacher)) {
            return true;
        }

        if ($user->isStudent()) {
            return $user->student?->current_class_id === $timetableEntry->school_class_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function update(User $user, TimetableEntry $timetableEntry): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function delete(User $user, TimetableEntry $timetableEntry): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function restore(User $user, TimetableEntry $timetableEntry): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }

    public function forceDelete(User $user, TimetableEntry $timetableEntry): bool
    {
        return $user->can(PermissionName::ManageTimetable->value);
    }
}
