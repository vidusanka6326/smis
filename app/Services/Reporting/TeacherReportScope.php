<?php

namespace App\Services\Reporting;

use App\Enums\TeacherAssignmentRole;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class TeacherReportScope
{
    /**
     * @return list<int>
     */
    public function accessibleClassIds(Teacher $teacher): array
    {
        return $teacher->homeroomClasses()->pluck('id')
            ->merge($teacher->assignments()->pluck('school_class_id'))
            ->unique()
            ->filter()
            ->values()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Subject IDs the teacher may report on (null subject assignments mean class teacher → all subjects in those classes).
     *
     * @return list<int>|null null means all subjects within accessible classes
     */
    public function accessibleSubjectIds(Teacher $teacher): ?array
    {
        if ($teacher->homeroomClasses()->exists()
            || $teacher->assignments()->where('role_in_assignment', TeacherAssignmentRole::ClassTeacher)->exists()) {
            return null;
        }

        return $teacher->assignments()
            ->whereNotNull('subject_id')
            ->pluck('subject_id')
            ->unique()
            ->filter()
            ->values()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return list<int>
     */
    public function accessibleStudentIds(Teacher $teacher, ?int $subjectId = null): array
    {
        $classIds = $this->accessibleClassIds($teacher);

        return Student::query()
            ->whereIn('current_class_id', $classIds)
            ->when($subjectId !== null, function ($q) use ($subjectId): void {
                $q->whereHas('currentClass.subjects', fn ($s) => $s->where('subjects.id', $subjectId));
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @return Collection<int, SchoolClass>
     */
    public function accessibleClasses(Teacher $teacher): Collection
    {
        return SchoolClass::query()
            ->whereIn('id', $this->accessibleClassIds($teacher))
            ->orderBy('code')
            ->get();
    }
}
