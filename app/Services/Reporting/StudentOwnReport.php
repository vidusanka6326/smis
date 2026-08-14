<?php

namespace App\Services\Reporting;

use App\Enums\AttendanceStatus;
use App\Models\Mark;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\Attendance\AttendancePercentageCalculator;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class StudentOwnReport
{
    public function __construct(
        private AttendancePercentageCalculator $attendanceCalculator,
        private AttendanceAnalyticsReport $attendanceAnalytics,
    ) {}

    /**
     * @return array{percentage: float, counts: array<string, int>, records: Collection<int, StudentAttendance>}
     */
    public function attendanceForMonth(Student $student, CarbonInterface $monthStart, CarbonInterface $monthEnd): array
    {
        $records = StudentAttendance::query()
            ->with(['attendanceSession.schoolClass', 'attendanceSession.subject'])
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function ($query) use ($monthStart, $monthEnd): void {
                $query->whereDate('date', '>=', $monthStart->toDateString())
                    ->whereDate('date', '<=', $monthEnd->toDateString());
            })
            ->get();

        return [
            'percentage' => $this->attendanceCalculator->percentage($records->pluck('status')->all()),
            'counts' => $this->attendanceAnalytics->countStatuses($records->pluck('status')),
            'records' => $records,
        ];
    }

    /**
     * @return array{overall_average: float|null, by_exam: list<array{exam_id: int|null, exam_name: string, average_percentage: float, rows: list<array{subject: string, marks_obtained: float, max_marks: float, percentage: float, grade_letter: string, is_pass: bool}>}>, marks: Collection<int, Mark>}
     */
    public function publishedResults(Student $student, ?int $examId = null): array
    {
        $marks = Mark::query()
            ->with(['examSubject.exam', 'examSubject.subject'])
            ->where('student_id', $student->id)
            ->whereHas('examSubject.exam', function ($query) use ($examId): void {
                $query->whereNotNull('published_at')
                    ->when($examId !== null, fn ($q) => $q->whereKey($examId));
            })
            ->get();

        $byExam = $marks
            ->groupBy(fn (Mark $mark) => $mark->examSubject?->exam_id)
            ->map(function (Collection $group): array {
                /** @var Collection<int, Mark> $group */
                $exam = $group->first()?->examSubject?->exam;
                $sumPct = 0.0;
                $rows = [];

                foreach ($group as $mark) {
                    $max = (float) ($mark->examSubject?->max_marks ?? 0);
                    $obtained = (float) $mark->marks_obtained;
                    $pct = $max > 0 ? round(($obtained / $max) * 100, 2) : 0.0;
                    $sumPct += $pct;
                    $rows[] = [
                        'subject' => $mark->examSubject?->subject?->name ?? '—',
                        'marks_obtained' => $obtained,
                        'max_marks' => $max,
                        'percentage' => $pct,
                        'grade_letter' => $mark->grade_letter->value,
                        'is_pass' => (bool) $mark->is_pass,
                    ];
                }

                $count = count($rows);

                return [
                    'exam_id' => $exam?->id,
                    'exam_name' => $exam?->name ?? __('Exam'),
                    'average_percentage' => $count > 0 ? round($sumPct / $count, 2) : 0.0,
                    'rows' => $rows,
                ];
            })
            ->values()
            ->all();

        $overallAverage = null;
        if ($marks->isNotEmpty()) {
            $sumPct = 0.0;
            foreach ($marks as $mark) {
                $max = (float) ($mark->examSubject?->max_marks ?? 0);
                $obtained = (float) $mark->marks_obtained;
                $sumPct += $max > 0 ? ($obtained / $max) * 100 : 0;
            }
            $overallAverage = round($sumPct / $marks->count(), 2);
        }

        return [
            'overall_average' => $overallAverage,
            'by_exam' => $byExam,
            'marks' => $marks,
        ];
    }

    /**
     * @return list<array{date: string, scope: string, subject: string, status: string}>
     */
    public function attendanceRows(Collection $records): array
    {
        return $records->map(function (StudentAttendance $record): array {
            $session = $record->attendanceSession;
            $status = $record->status instanceof AttendanceStatus
                ? $record->status->label()
                : (string) $record->status;

            return [
                'date' => $session?->date?->toDateString() ?? '—',
                'scope' => $session?->isClassSession() ? __('Class') : __('Subject'),
                'subject' => $session?->subject?->name ?? '—',
                'status' => $status,
            ];
        })->all();
    }
}
