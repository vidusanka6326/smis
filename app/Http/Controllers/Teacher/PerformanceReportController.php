<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\PerformanceRankingService;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\TeacherReportScope;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerformanceReportController extends Controller
{
    public function __invoke(
        Request $request,
        TeacherReportScope $scope,
        PerformanceRankingService $ranking,
        ReportCsvExporter $csv,
    ): View|StreamedResponse {
        $this->authorize('viewAny', Report::class);

        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403);

        $exams = Exam::query()->whereNotNull('published_at')->orderByDesc('published_at')->get();
        $examId = (int) $request->integer('exam_id', $exams->first()?->id ?? 0);
        $exam = $examId > 0 ? Exam::query()->with('examSubjects.subject')->findOrFail($examId) : null;
        $subjectId = $request->filled('subject_id') ? $request->integer('subject_id') : null;
        $subjectIds = $scope->accessibleSubjectIds($teacher);

        if ($subjectIds !== null && $subjectId !== null && ! in_array($subjectId, $subjectIds, true)) {
            abort(403);
        }

        if ($subjectIds !== null && $subjectId === null && count($subjectIds) === 1) {
            $subjectId = $subjectIds[0];
        }

        $limit = max(1, min(50, (int) $request->integer('limit', PerformanceRankingService::DEFAULT_LIMIT)));
        $ranks = $exam !== null
            ? $ranking->forExam($exam, $subjectId, $scope->accessibleStudentIds($teacher, $subjectId), $limit)
            : ['best' => [], 'poor' => []];

        if ($request->string('export')->toString() === 'csv') {
            $rows = collect($ranks['best'])->map(fn (array $row): array => [
                'best', $row['rank'], $row['name'], $row['percentage'],
            ]);

            return $csv->download('teacher-performance.csv', [__('Band'), __('Rank'), __('Student'), __('%')], $rows);
        }

        return view('teacher.reports.performance', [
            'exams' => $exams,
            'exam' => $exam,
            'ranks' => $ranks,
            'selectedExamId' => $examId,
            'selectedSubjectId' => $subjectId,
            'limit' => $limit,
            'print' => $request->boolean('print'),
        ]);
    }
}
