<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\User;

class AttendanceSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageAttendance->value)
            || $user->can(PermissionName::ViewAttendance->value);
    }

    public function view(User $user, AttendanceSession $attendanceSession): bool
    {
        if ($user->can(PermissionName::ManageAttendance->value) && $user->isSchoolOffice()) {
            return true;
        }

        if (! $user->can(PermissionName::ViewAttendance->value) && ! $user->can(PermissionName::ManageAttendance->value)) {
            return false;
        }

        if ($user->isTeacher() && $user->teacher) {
            return $user->teacher->canViewStudentAttendance(
                $attendanceSession->schoolClass,
                $attendanceSession->subject_id,
            );
        }

        if ($user->isStudent()) {
            return $user->student?->current_class_id === $attendanceSession->school_class_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageAttendance->value);
    }

    /**
     * Gate ability for creating a session in a specific class/subject context.
     * Call as: Gate::authorize('createForClass', [AttendanceSession::class, $schoolClass, $subjectId])
     */
    public function createForClass(User $user, SchoolClass $schoolClass, ?int $subjectId = null): bool
    {
        if (! $user->can(PermissionName::ManageAttendance->value)) {
            return false;
        }

        if ($user->isSchoolOffice()) {
            return true;
        }

        return $user->isTeacher()
            && $user->teacher?->canTakeStudentAttendance($schoolClass, $subjectId) === true;
    }

    public function update(User $user, AttendanceSession $attendanceSession): bool
    {
        if ($attendanceSession->isFinalized() && ! $user->isSchoolOffice() && ! $attendanceSession->date->isToday()) {
            return false;
        }

        if ($user->isSchoolOffice() && $user->can(PermissionName::ManageAttendance->value)) {
            return true;
        }

        if (! $user->can(PermissionName::ManageAttendance->value)) {
            return false;
        }

        return $user->isTeacher()
            && $user->teacher?->canTakeStudentAttendance(
                $attendanceSession->schoolClass,
                $attendanceSession->subject_id,
            ) === true;
    }

    public function finalize(User $user, AttendanceSession $attendanceSession): bool
    {
        return $this->update($user, $attendanceSession);
    }

    public function delete(User $user, AttendanceSession $attendanceSession): bool
    {
        return $user->isSchoolOffice() && $user->can(PermissionName::ManageAttendance->value);
    }

    public function restore(User $user, AttendanceSession $attendanceSession): bool
    {
        return $this->delete($user, $attendanceSession);
    }

    public function forceDelete(User $user, AttendanceSession $attendanceSession): bool
    {
        return $this->delete($user, $attendanceSession);
    }
}
