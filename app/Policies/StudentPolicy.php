<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageStudents->value)
            || $this->isClassTeacher($user);
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->can(PermissionName::ManageStudents->value)) {
            return true;
        }

        if ($user->isStudent() && $user->student?->is($student)) {
            return true;
        }

        return $this->teachesStudentClass($user, $student);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageStudents->value)
            || $this->isClassTeacher($user);
    }

    public function update(User $user, Student $student): bool
    {
        if ($user->can(PermissionName::ManageStudents->value)) {
            return true;
        }

        return $this->teachesStudentClass($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->can(PermissionName::ManageStudents->value);
    }

    public function restore(User $user, Student $student): bool
    {
        return $user->can(PermissionName::ManageStudents->value);
    }

    public function forceDelete(User $user, Student $student): bool
    {
        return $user->can(PermissionName::ManageStudents->value);
    }

    /**
     * Class teachers may only create students into their own homeroom classes.
     */
    public function createInClass(User $user, SchoolClass $schoolClass): bool
    {
        if ($user->can(PermissionName::ManageStudents->value)) {
            return true;
        }

        $teacher = $user->teacher;

        return $teacher !== null && $teacher->isClassTeacherOf($schoolClass);
    }

    private function isClassTeacher(User $user): bool
    {
        $teacher = $user->teacher;

        if ($teacher === null) {
            return false;
        }

        return $teacher->homeroomClasses()->exists()
            || $teacher->assignments()->where('role_in_assignment', 'class_teacher')->exists();
    }

    private function teachesStudentClass(User $user, Student $student): bool
    {
        if ($student->current_class_id === null) {
            return false;
        }

        $teacher = $user->teacher;

        if ($teacher === null) {
            return false;
        }

        $schoolClass = $student->currentClass;

        return $schoolClass !== null && $teacher->isClassTeacherOf($schoolClass);
    }
}
