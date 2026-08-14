<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\PerformanceRankingService;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PerformanceReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        PerformanceRankingService $ranking,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $exams = Exam::query()->whereNotNull('published_at')->orderByDesc('published_at')->get();
        $examId = (int) $request->integer('exam_id', $exams->first()?->id ?? 0);
        $exam = $examId > 0 ? Exam::query()->with('examSubjects.subject')->findOrFail($examId) : null;
        $subjectId = $request->filled('subject_id') ? $request->integer('subject_id') : null;
        $limit = max(1, min(50, (int) $request->integer('limit', PerformanceRankingService::DEFAULT_LIMIT)));

        $ranks = $exam !== null
            ? $ranking->forExam($exam, $subjectId, null, $limit)
            : ['best' => [], 'poor' => []];

        $headers = [__('Band'), __('Rank'), __('Student'), __('Class'), __('%'), __('Avg marks')];
        $rows = collect($ranks['best'])->map(fn (array $row): array => [
            'best',
            $row['rank'],
            $row['name'],
            $row['class'] ?? '—',
            $row['percentage'],
            $row['average_marks'],
        ])->merge(collect($ranks['poor'])->map(fn (array $row): array => [
            'poor',
            $row['rank'],
            $row['name'],
            $row['class'] ?? '—',
            $row['percentage'],
            $row['average_marks'],
        ]));

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            'performance-rankings',
            $headers,
            $rows,
            __('Best & poor performers'),
            [
                [
                    'title' => __('Best'),
                    'headers' => [__('Rank'), __('Student'), __('Class'), __('%')],
                    'rows' => collect($ranks['best'])->map(fn (array $row): array => [
                        $row['rank'],
                        $row['name'],
                        $row['class'] ?? '—',
                        $row['percentage'],
                    ])->all(),
                ],
                [
                    'title' => __('Needs improvement'),
                    'headers' => [__('Rank'), __('Student'), __('Class'), __('%')],
                    'rows' => collect($ranks['poor'])->map(fn (array $row): array => [
                        $row['rank'],
                        $row['name'],
                        $row['class'] ?? '—',
                        $row['percentage'],
                    ])->all(),
                ],
            ],
            $exam?->name,
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.performance', [
            'exams' => $exams,
            'exam' => $exam,
            'ranks' => $ranks,
            'selectedExamId' => $examId,
            'selectedSubjectId' => $subjectId,
            'limit' => $limit,
            'action' => route('admin.reports.performance'),
            'catalogRoute' => 'admin.reports.dashboard',
            'exportQuery' => ['exam_id' => $examId, 'subject_id' => $subjectId, 'limit' => $limit],
        ]);
    }
}
