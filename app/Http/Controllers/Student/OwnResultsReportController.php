<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Mark;
use App\Models\Report;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\StudentOwnReport;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OwnResultsReportController extends Controller
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

        $examId = $request->filled('exam_id') ? $request->integer('exam_id') : null;
        $results = $ownReport->publishedResults($student, $examId);

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
                $mark->is_pass ? __('Pass') : __('Fail'),
            ];
        });

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            'my-exam-results',
            $headers,
            $rows,
            __('My exam results'),
            [['title' => __('Published results'), 'headers' => $headers, 'rows' => $rows->all()]],
            $student->user?->name,
        );

        if ($exported !== null) {
            return $exported;
        }

        $flatRows = $results['marks']->map(function (Mark $mark): array {
            $max = (float) ($mark->examSubject?->max_marks ?? 0);
            $obtained = (float) $mark->marks_obtained;

            return [
                'exam' => $mark->examSubject?->exam?->name ?? '—',
                'subject' => $mark->examSubject?->subject?->name ?? '—',
                'marks_obtained' => $obtained,
                'max_marks' => $max,
                'percentage' => $max > 0 ? round(($obtained / $max) * 100, 2) : 0.0,
                'grade_letter' => $mark->grade_letter->value,
                'is_pass' => (bool) $mark->is_pass,
            ];
        })->values()->all();

        return view('student.reports.results', [
            'student' => $student->load(['currentClass', 'user']),
            'overallAverage' => $results['overall_average'],
            'rows' => ListQuery::paginateCollection($flatRows, $request),
            'exams' => Exam::query()
                ->whereNotNull('published_at')
                ->whereHas('examSubjects.marks', fn ($q) => $q->where('student_id', $student->id))
                ->orderByDesc('starts_on')
                ->get(),
            'selectedExamId' => $examId,
            'filters' => array_filter(['exam_id' => $examId], fn ($value) => filled($value)),
            'action' => route('student.reports.results'),
            'catalogRoute' => 'student.reports',
            'exportQuery' => ['exam_id' => $examId],
        ]);
    }
}
