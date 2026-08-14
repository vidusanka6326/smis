<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\ExaminationStatisticsReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ExaminationReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        ExaminationStatisticsReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $exams = Exam::query()->orderByDesc('starts_on')->get();
        $examId = (int) $request->integer('exam_id', $exams->firstWhere('published_at', '!=', null)?->id ?? $exams->first()?->id ?? 0);
        $exam = $examId > 0 ? Exam::query()->with('examSubjects.subject')->find($examId) : null;
        $subjectId = $request->filled('subject_id') ? $request->integer('subject_id') : null;

        $stats = $exam !== null ? $report->forExam($exam, $subjectId) : null;

        $headers = [__('Subject'), __('Entries'), __('Avg marks'), __('Pass %')];
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
            'examination-stats',
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
            'action' => route('admin.reports.examination'),
            'catalogRoute' => 'admin.reports.dashboard',
            'exportQuery' => ['exam_id' => $examId, 'subject_id' => $subjectId],
        ]);
    }
}
