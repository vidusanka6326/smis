<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\RespondsWithReportExport;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\Reporting\DemographicsReport;
use App\Services\Reporting\ReportCsvExporter;
use App\Services\Reporting\ReportPdfExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DemographicsReportController extends Controller
{
    use RespondsWithReportExport;

    public function __invoke(
        Request $request,
        DemographicsReport $report,
        ReportCsvExporter $csv,
        ReportPdfExporter $pdf,
    ): View|Response {
        $this->authorize('viewAny', Report::class);

        $subjectId = $request->filled('subject_id') ? $request->integer('subject_id') : null;
        $classIds = $request->filled('school_class_id')
            ? [$request->integer('school_class_id')]
            : null;

        $data = $report->summarize(
            schoolClassIds: $classIds,
            subjectId: $subjectId,
        );

        $headers = [__('Class'), __('Students')];
        $rows = collect($data['by_class'])->map(fn (array $row): array => [
            $row['code'],
            $row['count'],
        ]);

        $exported = $this->exportIfRequested(
            $request,
            $csv,
            $pdf,
            'demographics-by-class',
            $headers,
            $rows,
            __('Student demographics'),
            [
                [
                    'title' => __('By gender'),
                    'headers' => [__('Gender'), __('Count')],
                    'rows' => [
                        [__('Boys'), $data['by_gender']['B'] ?? 0],
                        [__('Girls'), $data['by_gender']['G'] ?? 0],
                    ],
                ],
                [
                    'title' => __('By class'),
                    'headers' => $headers,
                    'rows' => $rows->all(),
                ],
            ],
        );

        if ($exported !== null) {
            return $exported;
        }

        return view('reports.demographics', [
            'data' => $data,
            'filters' => array_filter([
                'school_class_id' => $classIds[0] ?? null,
                'subject_id' => $subjectId,
            ], fn ($value) => filled($value)),
            'schoolClasses' => SchoolClass::query()->orderBy('code')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(),
            'action' => route('admin.reports.demographics'),
            'catalogRoute' => 'admin.reports.dashboard',
            'exportQuery' => [
                'school_class_id' => $classIds[0] ?? null,
                'subject_id' => $subjectId,
            ],
        ]);
    }
}
