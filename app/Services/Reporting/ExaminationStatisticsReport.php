<?php

namespace App\Services\Reporting;

use App\Enums\GradeLetter;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Mark;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ExaminationStatisticsReport
{
    /**
     * Aggregate statistics for an exam (optionally filtered to a subject and/or student IDs).
     *
     * @param  list<int>|null  $studentIds
     * @return array{
     *     exam_id: int,
     *     subject_id: int|null,
     *     total_marks: int,
     *     pass_count: int,
     *     fail_count: int,
     *     pass_rate: float,
     *     average_marks: float,
     *     average_percentage: float,
     *     by_grade_letter: array<string, int>,
     *     by_subject: list<array{subject_id: int, subject: string, average_marks: float, pass_rate: float, count: int}>,
     *     by_class: list<array{school_class_id: int|null, code: string, average_percentage: float, pass_rate: float, count: int}>
     * }
     */
    public function forExam(Exam $exam, ?int $subjectId = null, ?array $studentIds = null): array
    {
        $exam->loadMissing('examSubjects.subject');

        $examSubjects = $exam->examSubjects
            ->when($subjectId !== null, fn (Collection $c) => $c->where('subject_id', $subjectId))
            ->values();

        if ($examSubjects->isEmpty()) {
            return $this->emptyResult($exam->id, $subjectId);
        }

        $marks = Mark::query()
            ->with(['examSubject.subject', 'student.user', 'student.currentClass'])
            ->whereIn('exam_subject_id', $examSubjects->pluck('id'))
            ->when($studentIds !== null, fn ($q) => $q->whereIn('student_id', $studentIds))
            ->get();

        return $this->summarizeMarks($exam->id, $subjectId, $marks, $examSubjects);
    }

    /**
     * Pure aggregation for unit testing without DB.
     *
     * @param  list<array{marks_obtained: float|int, max_marks: float|int, is_pass: bool, grade_letter: string|GradeLetter, subject_id?: int, subject?: string, school_class_id?: int|null, class_code?: string}>  $rows
     * @return array{
     *     total_marks: int,
     *     pass_count: int,
     *     fail_count: int,
     *     pass_rate: float,
     *     average_marks: float,
     *     average_percentage: float,
     *     by_grade_letter: array<string, int>,
     *     by_class: list<array{school_class_id: int|null, code: string, average_percentage: float, pass_rate: float, count: int}>
     * }
     */
    public function summarizeRows(array $rows): array
    {
        if ($rows === []) {
            return [
                'total_marks' => 0,
                'pass_count' => 0,
                'fail_count' => 0,
                'pass_rate' => 0.0,
                'average_marks' => 0.0,
                'average_percentage' => 0.0,
                'by_grade_letter' => $this->emptyGradeLetterCounts(),
                'by_class' => [],
            ];
        }

        $pass = 0;
        $fail = 0;
        $sumMarks = 0.0;
        $sumPct = 0.0;
        $letters = $this->emptyGradeLetterCounts();
        $byClassBuckets = [];

        foreach ($rows as $row) {
            $max = (float) $row['max_marks'];
            if ($max <= 0) {
                throw new InvalidArgumentException('Max marks must be greater than zero.');
            }

            $obtained = (float) $row['marks_obtained'];
            $pct = ($obtained / $max) * 100;
            $sumMarks += $obtained;
            $sumPct += $pct;

            if ($row['is_pass']) {
                $pass++;
            } else {
                $fail++;
            }

            $letter = $row['grade_letter'] instanceof GradeLetter
                ? $row['grade_letter']->value
                : (string) $row['grade_letter'];
            $letters[$letter] = ($letters[$letter] ?? 0) + 1;

            $classKey = array_key_exists('school_class_id', $row) && $row['school_class_id'] !== null
                ? (string) $row['school_class_id']
                : 'unassigned';
            $byClassBuckets[$classKey] ??= [
                'school_class_id' => $row['school_class_id'] ?? null,
                'code' => $row['class_code'] ?? 'Unassigned',
                'sum_pct' => 0.0,
                'pass' => 0,
                'count' => 0,
            ];
            $byClassBuckets[$classKey]['sum_pct'] += $pct;
            $byClassBuckets[$classKey]['count']++;
            if ($row['is_pass']) {
                $byClassBuckets[$classKey]['pass']++;
            }
        }

        $total = count($rows);
        $byClass = [];
        foreach ($byClassBuckets as $bucket) {
            $byClass[] = [
                'school_class_id' => $bucket['school_class_id'] !== null ? (int) $bucket['school_class_id'] : null,
                'code' => (string) $bucket['code'],
                'average_percentage' => round($bucket['sum_pct'] / $bucket['count'], 2),
                'pass_rate' => round(($bucket['pass'] / $bucket['count']) * 100, 2),
                'count' => $bucket['count'],
            ];
        }

        usort($byClass, fn (array $a, array $b): int => strcmp($a['code'], $b['code']));

        return [
            'total_marks' => $total,
            'pass_count' => $pass,
            'fail_count' => $fail,
            'pass_rate' => round(($pass / $total) * 100, 2),
            'average_marks' => round($sumMarks / $total, 2),
            'average_percentage' => round($sumPct / $total, 2),
            'by_grade_letter' => $letters,
            'by_class' => $byClass,
        ];
    }

    /**
     * @param  Collection<int, Mark>  $marks
     * @param  Collection<int, ExamSubject>  $examSubjects
     * @return array{
     *     exam_id: int,
     *     subject_id: int|null,
     *     total_marks: int,
     *     pass_count: int,
     *     fail_count: int,
     *     pass_rate: float,
     *     average_marks: float,
     *     average_percentage: float,
     *     by_grade_letter: array<string, int>,
     *     by_subject: list<array{subject_id: int, subject: string, average_marks: float, pass_rate: float, count: int}>,
     *     by_class: list<array{school_class_id: int|null, code: string, average_percentage: float, pass_rate: float, count: int}>
     * }
     */
    private function summarizeMarks(int $examId, ?int $subjectId, Collection $marks, Collection $examSubjects): array
    {
        $rows = $marks->map(function (Mark $mark): array {
            return [
                'marks_obtained' => (float) $mark->marks_obtained,
                'max_marks' => (float) $mark->examSubject->max_marks,
                'is_pass' => (bool) $mark->is_pass,
                'grade_letter' => $mark->grade_letter,
                'subject_id' => (int) $mark->examSubject->subject_id,
                'subject' => $mark->examSubject->subject?->name ?? '',
                'school_class_id' => $mark->student?->current_class_id,
                'class_code' => $mark->student?->currentClass?->code ?? 'Unassigned',
            ];
        })->all();

        $summary = $this->summarizeRows($rows);

        $bySubject = [];
        foreach ($marks->groupBy(fn (Mark $m) => $m->exam_subject_id) as $examSubjectId => $group) {
            /** @var Collection<int, Mark> $group */
            $examSubject = $examSubjects->firstWhere('id', (int) $examSubjectId) ?? $group->first()?->examSubject;
            $subjectRows = $group->map(fn (Mark $mark): array => [
                'marks_obtained' => (float) $mark->marks_obtained,
                'max_marks' => (float) ($examSubject?->max_marks ?? $mark->examSubject->max_marks),
                'is_pass' => (bool) $mark->is_pass,
                'grade_letter' => $mark->grade_letter,
            ])->all();
            $subjectSummary = $this->summarizeRows($subjectRows);
            $bySubject[] = [
                'subject_id' => (int) ($examSubject?->subject_id ?? 0),
                'subject' => $examSubject?->subject?->name ?? '',
                'average_marks' => $subjectSummary['average_marks'],
                'pass_rate' => $subjectSummary['pass_rate'],
                'count' => $subjectSummary['total_marks'],
            ];
        }

        return [
            'exam_id' => $examId,
            'subject_id' => $subjectId,
            ...$summary,
            'by_subject' => $bySubject,
        ];
    }

    /**
     * @return array{
     *     exam_id: int,
     *     subject_id: int|null,
     *     total_marks: int,
     *     pass_count: int,
     *     fail_count: int,
     *     pass_rate: float,
     *     average_marks: float,
     *     average_percentage: float,
     *     by_grade_letter: array<string, int>,
     *     by_subject: list<array{subject_id: int, subject: string, average_marks: float, pass_rate: float, count: int}>,
     *     by_class: list<array{school_class_id: int|null, code: string, average_percentage: float, pass_rate: float, count: int}>
     * }
     */
    private function emptyResult(int $examId, ?int $subjectId): array
    {
        return [
            'exam_id' => $examId,
            'subject_id' => $subjectId,
            'total_marks' => 0,
            'pass_count' => 0,
            'fail_count' => 0,
            'pass_rate' => 0.0,
            'average_marks' => 0.0,
            'average_percentage' => 0.0,
            'by_grade_letter' => $this->emptyGradeLetterCounts(),
            'by_subject' => [],
            'by_class' => [],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyGradeLetterCounts(): array
    {
        return [
            GradeLetter::A->value => 0,
            GradeLetter::B->value => 0,
            GradeLetter::C->value => 0,
            GradeLetter::S->value => 0,
            GradeLetter::F->value => 0,
        ];
    }
}
