<?php

namespace App\Http\Controllers\Student;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Report;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\StudentOwnReport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OwnReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        StudentOwnReport $ownReport,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewOwn', Report::class);

        $student = $request->user()->student;
        abort_unless($student !== null, 403);
        $student->load('currentClass.grade', 'user');

        [$month, $start, $end] = $this->monthRange($request);
        $attendance = $ownReport->attendanceForMonth($student, $start, $end);
        $results = $ownReport->publishedResults($student);

        $headers = [__('Exam'), __('Subject'), __('Marks'), __('Max'), __('%'), __('Grade'), __('Result')];
        $rows = $results['marks']->map(function (Mark $mark): array {
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

        $pdfTables = [];
        foreach ($results['by_exam'] as $examBlock) {
            $pdfTables[] = [
                'title' => $examBlock['exam_name'].' — '.__('average :pct%', ['pct' => $examBlock['average_percentage']]),
                'headers' => [__('Subject'), __('Marks'), __('Max'), __('%'), __('Grade'), __('Result')],
                'rows' => collect($examBlock['rows'])->map(fn (array $row): array => [
                    $row['subject'],
                    $row['marks_obtained'],
                    $row['max_marks'],
                    $row['percentage'],
                    $row['grade_letter'],
                    $row['is_pass'] ? __('Pass') : __('Fail'),
                ])->all(),
            ];
        }

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            'my-report-card',
            $headers,
            $rows,
            __('Report card'),
            $pdfTables !== [] ? $pdfTables : [[
                'title' => __('Published results'),
                'headers' => $headers,
                'rows' => [],
            ]],
            $student->user?->name.' — '.$month,
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('student.report', [
            'student' => $student,
            'month' => $month,
            'attendancePercentage' => $attendance['percentage'],
            'attendanceCounts' => $attendance['counts'],
            'attendanceRecords' => $attendance['records'],
            'marksByExam' => $results['by_exam'],
            'overallAverage' => $results['overall_average'],
            'presentKey' => AttendanceStatus::Present->value,
            'absentKey' => AttendanceStatus::Absent->value,
            'lateKey' => AttendanceStatus::Late->value,
            'excusedKey' => AttendanceStatus::Excused->value,
            'catalogRoute' => 'student.reports',
            'exportQuery' => ['month' => $month],
        ]);
    }
}
