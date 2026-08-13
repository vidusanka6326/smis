<?php

namespace App\Http\Controllers\Student;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Report;
use App\Models\StudentAttendance;
use App\Services\Attendance\AttendancePercentageCalculator;
use App\Services\Reporting\AttendanceAnalyticsReport;
use App\Services\Reporting\ReportCsvExporter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnReportController extends Controller
{
    public function __invoke(
        Request $request,
        AttendancePercentageCalculator $attendanceCalculator,
        AttendanceAnalyticsReport $attendanceAnalytics,
        ReportCsvExporter $csv,
    ): View|StreamedResponse {
        $this->authorize('viewOwn', Report::class);

        $student = $request->user()->student;
        abort_unless($student !== null, 403);

        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $attendanceRecords = StudentAttendance::query()
            ->with('attendanceSession')
            ->where('student_id', $student->id)
            ->whereHas('attendanceSession', function ($q) use ($start, $end): void {
                $q->whereDate('date', '>=', $start->toDateString())
                    ->whereDate('date', '<=', $end->toDateString());
            })
            ->get();

        $attendanceCounts = $attendanceAnalytics->countStatuses($attendanceRecords->pluck('status'));
        $attendancePercentage = $attendanceCalculator->percentage($attendanceRecords->pluck('status')->all());

        $marks = Mark::query()
            ->with(['examSubject.exam', 'examSubject.subject'])
            ->where('student_id', $student->id)
            ->whereHas('examSubject.exam', fn ($q) => $q->whereNotNull('published_at'))
            ->get();

        $marksByExam = $marks
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

        if ($request->string('export')->toString() === 'csv') {
            $rows = $marks->map(function (Mark $mark): array {
                $max = (float) ($mark->examSubject?->max_marks ?? 0);
                $obtained = (float) $mark->marks_obtained;
                $pct = $max > 0 ? round(($obtained / $max) * 100, 2) : 0.0;

                return [
                    $mark->examSubject?->exam?->name,
                    $mark->examSubject?->subject?->name,
                    $obtained,
                    $max,
                    $pct,
                    $mark->grade_letter->value,
                    $mark->is_pass ? 'pass' : 'fail',
                ];
            });

            return $csv->download(
                'my-results.csv',
                [__('Exam'), __('Subject'), __('Marks'), __('Max'), __('%'), __('Grade'), __('Result')],
                $rows,
            );
        }

        return view('student.report', [
            'student' => $student->load('currentClass.grade'),
            'month' => $month,
            'attendancePercentage' => $attendancePercentage,
            'attendanceCounts' => $attendanceCounts,
            'attendanceRecords' => $attendanceRecords,
            'marksByExam' => $marksByExam,
            'overallAverage' => $overallAverage,
            'print' => $request->boolean('print'),
            'presentKey' => AttendanceStatus::Present->value,
            'absentKey' => AttendanceStatus::Absent->value,
            'lateKey' => AttendanceStatus::Late->value,
            'excusedKey' => AttendanceStatus::Excused->value,
        ]);
    }
}
