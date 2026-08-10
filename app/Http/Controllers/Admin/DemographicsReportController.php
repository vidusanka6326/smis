<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Reporting\DemographicsReport;
use App\Services\Reporting\ReportCsvExporter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DemographicsReportController extends Controller
{
    public function __invoke(Request $request, DemographicsReport $report, ReportCsvExporter $csv): View|StreamedResponse
    {
        $this->authorize('viewAny', Report::class);

        $data = $report->summarize(
            subjectId: $request->filled('subject_id') ? $request->integer('subject_id') : null,
        );

        if ($request->string('export')->toString() === 'csv') {
            $rows = collect($data['by_class'])->map(fn (array $row): array => [
                $row['code'],
                $row['count'],
            ]);

            return $csv->download('demographics-by-class.csv', [__('Class'), __('Students')], $rows);
        }

        return view('admin.reports.demographics', [
            'data' => $data,
            'print' => $request->boolean('print'),
        ]);
    }
}
