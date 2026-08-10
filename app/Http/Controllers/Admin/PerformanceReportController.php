<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\PerformanceRankingService;
use App\Services\Reporting\ReportCsvExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerformanceReportController extends Controller
{
    public function __invoke(Request $request, PerformanceRankingService $ranking, ReportCsvExporter $csv): View|StreamedResponse
    {
        $this->authorize('viewAny', Report::class);

        $exams = Exam::query()->whereNotNull('published_at')->orderByDesc('published_at')->get();
        $examId = (int) $request->integer('exam_id', $exams->first()?->id ?? 0);
        $exam = $examId > 0 ? Exam::query()->with('examSubjects.subject')->findOrFail($examId) : null;
        $subjectId = $request->filled('subject_id') ? $request->integer('subject_id') : null;
        $limit = max(1, min(50, (int) $request->integer('limit', PerformanceRankingService::DEFAULT_LIMIT)));

        $ranks = $exam !== null
            ? $ranking->forExam($exam, $subjectId, null, $limit)
            : ['best' => [], 'poor' => []];

        if ($request->string('export')->toString() === 'csv') {
            $rows = collect($ranks['best'])->map(fn (array $row): array => [
                'best',
                $row['rank'],
                $row['name'],
                $row['percentage'],
                $row['average_marks'],
            ])->merge(collect($ranks['poor'])->map(fn (array $row): array => [
                'poor',
                $row['rank'],
                $row['name'],
                $row['percentage'],
                $row['average_marks'],
            ]));

            return $csv->download(
                'performance-rankings.csv',
                [__('Band'), __('Rank'), __('Student'), __('%'), __('Avg marks')],
                $rows,
            );
        }

        return view('admin.reports.performance', [
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
