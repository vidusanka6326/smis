<?php

namespace App\Actions\Teachers;

use App\Enums\TeacherAssignmentRole;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SyncTeacherAssignments
{
    /**
     * Replace a teacher's assignments for an academic year and sync homeroom class_teacher_id.
     *
     * @param  list<array{school_class_id: int, subject_id?: int|null, role_in_assignment: string}>  $assignments
     */
    public function handle(Teacher $teacher, int $academicYearId, array $assignments): Teacher
    {
        return DB::transaction(function () use ($teacher, $academicYearId, $assignments): Teacher {
            TeacherAssignment::query()
                ->where('teacher_id', $teacher->id)
                ->where('academic_year_id', $academicYearId)
                ->delete();

            SchoolClass::query()
                ->where('class_teacher_id', $teacher->id)
                ->where('academic_year_id', $academicYearId)
                ->update(['class_teacher_id' => null]);

            $seen = [];

            foreach ($assignments as $assignment) {
                $role = TeacherAssignmentRole::tryFrom($assignment['role_in_assignment']);

                if ($role === null) {
                    throw ValidationException::withMessages([
                        'assignments' => __('One or more assignment roles are invalid.'),
                    ]);
                }

                $schoolClass = SchoolClass::query()->with('grade')->find($assignment['school_class_id']);

                if ($schoolClass === null || (int) $schoolClass->academic_year_id !== $academicYearId) {
                    throw ValidationException::withMessages([
                        'assignments' => __('Each assignment class must belong to the selected academic year.'),
                    ]);
                }

                $subjectId = $assignment['subject_id'] ?? null;

                if ($role->requiresSubject()) {
                    if (blank($subjectId)) {
                        throw ValidationException::withMessages([
                            'assignments' => __('Subject teacher assignments require a subject.'),
                        ]);
                    }

                    $subject = Subject::query()->find($subjectId);

                    if ($subject === null || ! $subject->appliesToGrade($schoolClass->grade->number)) {
                        throw ValidationException::withMessages([
                            'assignments' => __('Assigned subjects must apply to the class grade.'),
                        ]);
                    }

                    if (! $schoolClass->subjects()->whereKey($subjectId)->exists()) {
                        throw ValidationException::withMessages([
                            'assignments' => __('Subject must already be linked to the class.'),
                        ]);
                    }
                } else {
                    $subjectId = null;
                }

                $key = implode('|', [$schoolClass->id, (string) $subjectId, $role->value]);

                if (isset($seen[$key])) {
                    throw ValidationException::withMessages([
                        'assignments' => __('Duplicate assignment detected: the same class, role, and subject combination cannot be added more than once.'),
                    ]);
                }

                $seen[$key] = true;

                try {
                    TeacherAssignment::query()->create([
                        'teacher_id' => $teacher->id,
                        'school_class_id' => $schoolClass->id,
                        'subject_id' => $subjectId,
                        'academic_year_id' => $academicYearId,
                        'role_in_assignment' => $role,
                    ]);
                } catch (UniqueConstraintViolationException) {
                    throw ValidationException::withMessages([
                        'assignments' => __('Duplicate assignment detected: the same class, role, and subject combination already exists.'),
                    ]);
                }

                if ($role === TeacherAssignmentRole::ClassTeacher) {
                    $schoolClass->forceFill(['class_teacher_id' => $teacher->id])->save();
                }
            }

            return $teacher->refresh()->load(['assignments.schoolClass', 'assignments.subject', 'assignments.academicYear']);
        });
    }
}
