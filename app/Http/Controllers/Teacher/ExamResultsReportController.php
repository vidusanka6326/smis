<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\ExamResultsReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\TeacherReportScope;
use App\Support\ListQuery;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ExamResultsReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        TeacherReportScope $scope,
        ExamResultsReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $exams = Exam::query()->whereNotNull('published_at')->orderByDesc('published_at')->get();
        $examId = (int) $request->integer('exam_id', $exams->first()?->id ?? 0);
        $exam = $examId > 0 ? Exam::query()->with('examSubjects.subject')->find($examId) : null;

        $subjectIds = $scope->accessibleSubjectIds($teacher);
        $subjectId = $request->filled('subject_id') ? $request->integer('subject_id') : null;

        if ($subjectIds !== null && $subjectId !== null && ! in_array($subjectId, $subjectIds, true)) {
            abort(403);
        }

        if ($subjectIds !== null && $subjectId === null && count($subjectIds) === 1) {
            $subjectId = $subjectIds[0];
        }

        $result = $request->string('result')->toString() ?: null;

        $results = $exam !== null
            ? $report->forExam($exam, $subjectId, $scope->accessibleStudentIds($teacher, $subjectId), $result)
            : [];

        $headers = [__('Student'), __('Admission no.'), __('Class'), __('Subject'), __('Marks'), __('Max'), __('%'), __('Grade'), __('Result')];
        $rows = collect($results)->map(fn (array $row): array => [
            $row['student'],
            $row['admission_no'],
            $row['class'],
            $row['subject'],
            $row['marks_obtained'],
            $row['max_marks'],
            $row['percentage'],
            $row['grade_letter'],
            $row['result'],
        ]);

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            'teacher-exam-results',
            $headers,
            $rows,
            __('Exam results'),
            [['title' => $exam?->name ?? __('Exam results'), 'headers' => $headers, 'rows' => $rows->all()]],
            $exam?->name,
            'landscape',
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.exam-results', [
            'exams' => $exams,
            'exam' => $exam,
            'rows' => ListQuery::paginateCollection($results, $request),
            'selectedExamId' => $examId,
            'selectedSubjectId' => $subjectId,
            'selectedResult' => $result,
            'filters' => array_filter([
                'exam_id' => $examId ?: null,
                'subject_id' => $subjectId,
                'result' => $result,
            ], fn ($value) => filled($value)),
            'action' => route('teacher.reports.exam-results'),
            'catalogRoute' => 'teacher.reports.dashboard',
            'exportQuery' => [
                'exam_id' => $examId,
                'subject_id' => $subjectId,
                'result' => $result,
            ],
        ]);
    }
}
