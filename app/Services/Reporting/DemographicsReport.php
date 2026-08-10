<?php

namespace App\Services\Reporting;

use App\Enums\Gender;
use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;

class DemographicsReport
{
    /**
     * @param  list<int>|null  $schoolClassIds  Restrict to these classes (teacher scope).
     * @return array{
     *     total: int,
     *     by_gender: array<string, int>,
     *     by_grade: list<array{grade_id: int|null, grade: string, count: int}>,
     *     by_class: list<array{school_class_id: int, code: string, count: int}>,
     *     by_subject: list<array{subject_id: int, subject: string, count: int}>
     * }
     */
    public function summarize(?array $schoolClassIds = null, ?int $subjectId = null): array
    {
        $students = Student::query()
            ->with(['currentClass.grade', 'currentClass.subjects', 'user'])
            ->when($schoolClassIds !== null, fn ($q) => $q->whereIn('current_class_id', $schoolClassIds))
            ->when($subjectId !== null, function ($q) use ($subjectId): void {
                $q->whereHas('currentClass.subjects', fn ($s) => $s->where('subjects.id', $subjectId));
            })
            ->get();

        $byGender = [
            Gender::Boy->value => 0,
            Gender::Girl->value => 0,
        ];

        foreach ($students as $student) {
            $byGender[$student->gender->value] = ($byGender[$student->gender->value] ?? 0) + 1;
        }

        $byGrade = $students
            ->groupBy(fn (Student $s) => $s->currentClass?->grade_id)
            ->map(function (Collection $group, $gradeId): array {
                /** @var Student|null $first */
                $first = $group->first();

                return [
                    'grade_id' => $gradeId !== '' ? (int) $gradeId : null,
                    'grade' => $first?->currentClass?->grade?->name ?? __('Unassigned'),
                    'count' => $group->count(),
                ];
            })
            ->sortBy('grade')
            ->values()
            ->all();

        $byClass = $students
            ->filter(fn (Student $s) => $s->current_class_id !== null)
            ->groupBy('current_class_id')
            ->map(function (Collection $group, $classId): array {
                /** @var Student $first */
                $first = $group->first();

                return [
                    'school_class_id' => (int) $classId,
                    'code' => $first->currentClass?->code ?? (string) $classId,
                    'count' => $group->count(),
                ];
            })
            ->sortBy('code')
            ->values()
            ->all();

        $subjectCounts = [];
        foreach ($students as $student) {
            foreach ($student->currentClass?->subjects ?? [] as $subject) {
                if ($subjectId !== null && (int) $subject->id !== $subjectId) {
                    continue;
                }
                $subjectCounts[$subject->id] ??= ['subject_id' => $subject->id, 'subject' => $subject->name, 'count' => 0];
                $subjectCounts[$subject->id]['count']++;
            }
        }

        return [
            'total' => $students->count(),
            'by_gender' => $byGender,
            'by_grade' => $byGrade,
            'by_class' => $byClass,
            'by_subject' => collect($subjectCounts)->sortBy('subject')->values()->all(),
        ];
    }
}
