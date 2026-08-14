<?php

namespace App\Services\Reporting;

use App\Models\TeacherAssignment;

class TeacherAssignmentReport
{
    /**
     * @return list<array{teacher: string, employee_no: string, class: string, subject: string, role: string, academic_year: string}>
     */
    public function rows(?int $academicYearId = null, ?int $schoolClassId = null, ?string $role = null): array
    {
        $assignments = TeacherAssignment::query()
            ->with(['teacher.user', 'schoolClass', 'subject', 'academicYear'])
            ->when($academicYearId !== null, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($schoolClassId !== null, fn ($q) => $q->where('school_class_id', $schoolClassId))
            ->when($role !== null, fn ($q) => $q->where('role_in_assignment', $role))
            ->latest('id')
            ->get()
            ->sortBy(fn (TeacherAssignment $assignment): string => ($assignment->teacher?->user?->name ?? '').'|'.($assignment->schoolClass?->code ?? ''))
            ->values();

        return $assignments->map(function (TeacherAssignment $assignment): array {
            return [
                'teacher' => $assignment->teacher?->user?->name ?? '—',
                'employee_no' => (string) ($assignment->teacher?->employee_no ?? '—'),
                'class' => $assignment->schoolClass?->code ?? '—',
                'subject' => $assignment->subject?->name ?? '—',
                'role' => $assignment->role_in_assignment->label(),
                'academic_year' => $assignment->academicYear?->name ?? '—',
            ];
        })->all();
    }
}
