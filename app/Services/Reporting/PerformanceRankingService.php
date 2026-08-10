<?php

namespace App\Services\Reporting;

use App\Models\Exam;
use App\Models\Mark;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Ranks students by average percentage across exam subjects.
 *
 * Assumption: best/poor performers use top/bottom N by average % (default 5).
 */
class PerformanceRankingService
{
    public const DEFAULT_LIMIT = 5;

    /**
     * @param  list<array{student_id: int, name: string, percentage: float, average_marks: float, is_pass_all?: bool}>  $rows
     * @return array{best: list<array{student_id: int, name: string, percentage: float, average_marks: float, rank: int}>, poor: list<array{student_id: int, name: string, percentage: float, average_marks: float, rank: int}>}
     */
    public function rank(array $rows, int $limit = self::DEFAULT_LIMIT): array
    {
        if ($limit < 1) {
            throw new InvalidArgumentException('Limit must be at least 1.');
        }

        $sortedDesc = $rows;
        usort($sortedDesc, function (array $a, array $b): int {
            $cmp = $b['percentage'] <=> $a['percentage'];
            if ($cmp !== 0) {
                return $cmp;
            }

            return $b['average_marks'] <=> $a['average_marks'];
        });

        $best = [];
        foreach (array_slice($sortedDesc, 0, $limit) as $index => $row) {
            $best[] = [
                'student_id' => $row['student_id'],
                'name' => $row['name'],
                'percentage' => $row['percentage'],
                'average_marks' => $row['average_marks'],
                'rank' => $index + 1,
            ];
        }

        $sortedAsc = $rows;
        usort($sortedAsc, function (array $a, array $b): int {
            $cmp = $a['percentage'] <=> $b['percentage'];
            if ($cmp !== 0) {
                return $cmp;
            }

            return $a['average_marks'] <=> $b['average_marks'];
        });

        $poor = [];
        foreach (array_slice($sortedAsc, 0, $limit) as $index => $row) {
            $poor[] = [
                'student_id' => $row['student_id'],
                'name' => $row['name'],
                'percentage' => $row['percentage'],
                'average_marks' => $row['average_marks'],
                'rank' => $index + 1,
            ];
        }

        return [
            'best' => $best,
            'poor' => $poor,
        ];
    }

    /**
     * @param  list<int>|null  $studentIds
     * @return array{best: list<array{student_id: int, name: string, percentage: float, average_marks: float, rank: int}>, poor: list<array{student_id: int, name: string, percentage: float, average_marks: float, rank: int}>}
     */
    public function forExam(Exam $exam, ?int $subjectId = null, ?array $studentIds = null, int $limit = self::DEFAULT_LIMIT): array
    {
        $exam->loadMissing('examSubjects');

        $examSubjectIds = $exam->examSubjects
            ->when($subjectId !== null, fn (Collection $c) => $c->where('subject_id', $subjectId))
            ->pluck('id');

        $marks = Mark::query()
            ->with(['student.user', 'examSubject'])
            ->whereIn('exam_subject_id', $examSubjectIds)
            ->when($studentIds !== null, fn ($q) => $q->whereIn('student_id', $studentIds))
            ->get()
            ->groupBy('student_id');

        $rows = [];

        foreach ($marks as $studentId => $group) {
            /** @var Collection<int, Mark> $group */
            $sumPct = 0.0;
            $sumMarks = 0.0;
            foreach ($group as $mark) {
                $max = (float) $mark->examSubject->max_marks;
                $obtained = (float) $mark->marks_obtained;
                $sumPct += $max > 0 ? ($obtained / $max) * 100 : 0;
                $sumMarks += $obtained;
            }
            $count = $group->count();
            $rows[] = [
                'student_id' => (int) $studentId,
                'name' => $group->first()?->student?->user?->name ?? (string) $studentId,
                'percentage' => round($sumPct / $count, 2),
                'average_marks' => round($sumMarks / $count, 2),
            ];
        }

        return $this->rank($rows, $limit);
    }
}
