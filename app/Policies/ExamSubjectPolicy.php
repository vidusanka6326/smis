<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\ExamSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;

class ExamSubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ManageExaminations->value)
            || $user->can(PermissionName::EnterMarks->value)
            || $user->can(PermissionName::ViewMarks->value);
    }

    public function view(User $user, ExamSubject $examSubject): bool
    {
        if ($user->can(PermissionName::ManageExaminations->value)) {
            return true;
        }

        if ($user->isStudent()) {
            return $user->can(PermissionName::ViewMarks->value) && $examSubject->exam->isPublished();
        }

        if ($user->isTeacher() && $user->teacher) {
            return $this->teacherCanAccess($user, $examSubject, viewOnly: true);
        }

        return false;
    }

    public function update(User $user, ExamSubject $examSubject): bool
    {
        return $user->can(PermissionName::ManageExaminations->value);
    }

    public function enterMarks(User $user, ExamSubject $examSubject): bool
    {
        if ($examSubject->exam->isPublished()) {
            return false;
        }

        if ($user->isAdmin() && $user->can(PermissionName::EnterMarks->value)) {
            return true;
        }

        if (! $user->can(PermissionName::EnterMarks->value)) {
            return false;
        }

        return $user->isTeacher() && $user->teacher && $this->teacherCanAccess($user, $examSubject, viewOnly: false);
    }

    private function teacherCanAccess(User $user, ExamSubject $examSubject, bool $viewOnly): bool
    {
        $teacher = $user->teacher;
        $exam = $examSubject->exam()->first() ?? $examSubject->exam;
        $subjectId = (int) $examSubject->subject_id;

        if ($exam->school_class_id !== null) {
            $schoolClass = SchoolClass::query()->find($exam->school_class_id);

            if ($schoolClass === null) {
                return false;
            }

            return $viewOnly
                ? $teacher->canViewMarksFor($schoolClass, $subjectId)
                : $teacher->canEnterMarksFor($schoolClass, $subjectId);
        }

        $classIds = Student::query()
            ->whereHas('currentClass', function ($q) use ($exam): void {
                $q->where('academic_year_id', $exam->academic_year_id)
                    ->when($exam->grade_id !== null, fn ($inner) => $inner->where('grade_id', $exam->grade_id));
            })
            ->pluck('current_class_id')
            ->unique()
            ->filter();

        foreach ($classIds as $classId) {
            $schoolClass = SchoolClass::query()->find($classId);

            if ($schoolClass === null) {
                continue;
            }

            $allowed = $viewOnly
                ? $teacher->canViewMarksFor($schoolClass, $subjectId)
                : $teacher->canEnterMarksFor($schoolClass, $subjectId);

            if ($allowed) {
                return true;
            }
        }

        return false;
    }
}
