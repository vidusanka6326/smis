<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\ExaminationStatisticsReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\TeacherReportScope;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExaminationReportController extends Controller
{
    public function __invoke(
        Request $request,
        TeacherReportScope $scope,
        ExaminationStatisticsReport $report,
        ReportCsvExporter $csv,
    ): View|StreamedResponse {
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

        if ($request->string('export')->toString() === 'csv' && $stats !== null) {
            $rows = collect($stats['by_subject'])->map(fn (array $row): array => [
                $row['subject'],
                $row['count'],
                $row['average_marks'],
                $row['pass_rate'],
            ]);

            return $csv->download('teacher-examination-stats.csv', [__('Subject'), __('Entries'), __('Avg'), __('Pass %')], $rows);
        }

        return view('teacher.reports.examination', [
            'exams' => $exams,
            'exam' => $exam,
            'stats' => $stats,
            'selectedExamId' => $examId,
            'selectedSubjectId' => $subjectId,
            'print' => $request->boolean('print'),
        ]);
    }
}
