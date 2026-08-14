<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\ExaminationStatisticsReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use App\Services\Reporting\TeacherReportScope;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ExaminationReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        TeacherReportScope $scope,
        ExaminationStatisticsReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $exams = Exam::query()->whereNotNull('published_at')->orderByDesc('starts_on')->get();
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

        $stats = $exam !== null
            ? $report->forExam($exam, $subjectId, $scope->accessibleStudentIds($teacher, $subjectId))
            : null;

        $headers = [__('Subject'), __('Entries'), __('Avg'), __('Pass %')];
        $rows = collect($stats['by_subject'] ?? [])->map(fn (array $row): array => [
            $row['subject'],
            $row['count'],
            $row['average_marks'],
            $row['pass_rate'],
        ]);

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            'teacher-examination-stats',
            $headers,
            $rows,
            __('Examination statistics'),
            [
                [
                    'title' => __('By subject'),
                    'headers' => $headers,
                    'rows' => $rows->all(),
                ],
                [
                    'title' => __('By class'),
                    'headers' => [__('Class'), __('Entries'), __('Avg %'), __('Pass %')],
                    'rows' => collect($stats['by_class'] ?? [])->map(fn (array $row): array => [
                        $row['code'],
                        $row['count'],
                        $row['average_percentage'],
                        $row['pass_rate'],
                    ])->all(),
                ],
            ],
            $exam?->name,
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.examination', [
            'exams' => $exams,
            'exam' => $exam,
            'stats' => $stats,
            'selectedExamId' => $examId,
            'selectedSubjectId' => $subjectId,
            'action' => route('teacher.reports.examination'),
            'catalogRoute' => 'teacher.reports.dashboard',
            'exportQuery' => ['exam_id' => $examId, 'subject_id' => $subjectId],
        ]);
    }
}
