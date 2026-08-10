<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Report;
use App\Services\Reporting\ExaminationStatisticsReport;
use App\Services\Reporting\ReportCsvExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExaminationReportController extends Controller
{
    public function __invoke(Request $request, ExaminationStatisticsReport $report, ReportCsvExporter $csv): View|StreamedResponse
    {
        $this->authorize('viewAny', Report::class);

        $exams = Exam::query()->orderByDesc('starts_on')->get();
        $examId = (int) $request->integer('exam_id', $exams->firstWhere('published_at', '!=', null)?->id ?? $exams->first()?->id ?? 0);
        $exam = $examId > 0 ? Exam::query()->with('examSubjects.subject')->find($examId) : null;
        $subjectId = $request->filled('subject_id') ? $request->integer('subject_id') : null;

        $stats = $exam !== null ? $report->forExam($exam, $subjectId) : null;

        if ($request->string('export')->toString() === 'csv' && $stats !== null) {
            $rows = collect($stats['by_subject'])->map(fn (array $row): array => [
                $row['subject'],
                $row['count'],
                $row['average_marks'],
                $row['pass_rate'],
            ]);

            return $csv->download(
                'examination-stats.csv',
                [__('Subject'), __('Entries'), __('Avg marks'), __('Pass %')],
                $rows,
            );
        }

        return view('admin.reports.examination', [
            'exams' => $exams,
            'exam' => $exam,
            'stats' => $stats,
            'selectedExamId' => $examId,
            'selectedSubjectId' => $subjectId,
            'print' => $request->boolean('print'),
        ]);
    }
}
