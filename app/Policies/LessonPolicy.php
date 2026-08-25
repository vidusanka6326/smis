<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'officer', 'teacher', 'student']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Lesson $lesson): bool
    {
        if ($user->hasRole(['admin', 'officer'])) {
            return true;
        }

        if ($user->hasRole('teacher') && $user->teacher) {
            return $lesson->teacher_id === $user->teacher->id || $lesson->schoolClasses->contains(fn ($class) => $user->teacher->isClassTeacherOf($class));
        }

        if ($user->hasRole('student') && $user->student) {
            return $lesson->schoolClasses->contains('id', $user->student->current_class_id);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        if ($user->hasRole('teacher') && $user->teacher) {
            return $lesson->teacher_id === $user->teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        if ($user->hasRole(['admin', 'officer'])) {
            return true;
        }

        if ($user->hasRole('teacher') && $user->teacher) {
            return $lesson->teacher_id === $user->teacher->id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Lesson $lesson): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Lesson $lesson): bool
    {
        return false;
    }
}
