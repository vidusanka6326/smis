<?php

namespace App\Services\Reporting;

use App\Models\Exam;
use App\Models\Mark;

class ExamResultsReport
{
    /**
     * @param  list<int>|null  $studentIds
     * @return list<array{student: string, admission_no: string, class: string, subject: string, marks_obtained: float, max_marks: float, percentage: float, grade_letter: string, result: string}>
     */
    public function forExam(Exam $exam, ?int $subjectId = null, ?array $studentIds = null, ?string $result = null): array
    {
        $marks = Mark::query()
            ->with(['student.user', 'student.currentClass', 'examSubject.subject'])
            ->whereHas('examSubject', function ($query) use ($exam, $subjectId): void {
                $query->where('exam_id', $exam->id)
                    ->when($subjectId !== null, fn ($q) => $q->where('subject_id', $subjectId));
            })
            ->when($studentIds !== null, fn ($q) => $q->whereIn('student_id', $studentIds))
            ->when($result === 'pass', fn ($q) => $q->where('is_pass', true))
            ->when($result === 'fail', fn ($q) => $q->where('is_pass', false))
            ->get()
            ->sortBy(fn (Mark $mark): string => ($mark->student?->currentClass?->code ?? '').'|'.($mark->student?->user?->name ?? '').'|'.($mark->examSubject?->subject?->name ?? ''))
            ->values();

        return $marks->map(function (Mark $mark): array {
            $max = (float) ($mark->examSubject?->max_marks ?? 0);
            $obtained = (float) $mark->marks_obtained;
            $percentage = $max > 0 ? round(($obtained / $max) * 100, 2) : 0.0;

            return [
                'student' => $mark->student?->user?->name ?? '—',
                'admission_no' => (string) ($mark->student?->admission_no ?? '—'),
                'class' => $mark->student?->currentClass?->code ?? '—',
                'subject' => $mark->examSubject?->subject?->name ?? '—',
                'marks_obtained' => $obtained,
                'max_marks' => $max,
                'percentage' => $percentage,
                'grade_letter' => $mark->grade_letter->value,
                'result' => $mark->is_pass ? __('Pass') : __('Fail'),
            ];
        })->all();
    }
}
